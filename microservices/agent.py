from __future__ import annotations

import json
import os
from typing import Any
from urllib import error, request

from dotenv import load_dotenv

from internal_http import urlopen_internal

from livekit.agents import (
    Agent,
    AgentSession,
    AutoSubscribe,
    JobContext,
    WorkerOptions,
    cli,
)
from livekit.plugins import openai, silero
from prompts import DEFAULT_INTERVIEW_CONFIG, WELCOME_MESSAGE, build_system_prompt

load_dotenv()


def _env_float(name: str, default: float) -> float:
    raw = (os.getenv(name) or "").strip()
    if raw == "":
        return default
    try:
        return float(raw)
    except ValueError:
        return default


def _env_optional_float(name: str) -> float | None:
    raw = (os.getenv(name) or "").strip()
    if raw == "":
        return None
    try:
        return float(raw)
    except ValueError:
        return None


def _silero_vad():
    """
    Build VAD with optional env tuning.
    Falls back to plugin defaults if kwargs are unsupported by the installed version.
    """
    kwargs: dict[str, Any] = {}
    for env_name, option_key in (
        ("SILERO_VAD_MIN_SPEECH_DURATION", "min_speech_duration"),
        ("SILERO_VAD_MIN_SILENCE_DURATION", "min_silence_duration"),
        ("SILERO_VAD_PREFIX_PADDING_DURATION", "prefix_padding_duration"),
        ("SILERO_VAD_MAX_BUFFERED_SPEECH", "max_buffered_speech"),
        ("SILERO_VAD_ACTIVATION_THRESHOLD", "activation_threshold"),
    ):
        value = _env_optional_float(env_name)
        if value is not None:
            kwargs[option_key] = value

    try:
        return silero.VAD.load(**kwargs)
    except TypeError:
        return silero.VAD.load()


def _openai_stt(api_key: str) -> openai.STT:
    kwargs: dict[str, Any] = {
        "api_key": api_key,
    }
    model = (os.getenv("OPENAI_STT_MODEL") or "").strip()
    language = (os.getenv("OPENAI_STT_LANGUAGE") or "").strip()
    prompt = (os.getenv("OPENAI_STT_PROMPT") or "").strip()

    if model:
        kwargs["model"] = model
    if language:
        kwargs["language"] = language
    if prompt:
        kwargs["prompt"] = prompt

    try:
        return openai.STT(**kwargs)
    except TypeError:
        return openai.STT(api_key=api_key)


def _openai_tts(api_key: str) -> openai.TTS:
    """
    OpenAI TTS defaults (gpt-4o-mini-tts + mp3 over SSE) can sound choppy when streamed in
    small chunks. Prefer raw PCM; tune model/voice via env (see microservices/.env.example).
    """
    kwargs: dict[str, Any] = {
        "api_key": api_key,
        "model": os.getenv("OPENAI_TTS_MODEL", "gpt-4o-mini-tts"),
        "voice": os.getenv("OPENAI_TTS_VOICE", "ash"),
        "speed": _env_float("OPENAI_TTS_SPEED", 1.0),
        "response_format": os.getenv("OPENAI_TTS_RESPONSE_FORMAT", "pcm"),
    }
    instructions = os.getenv("OPENAI_TTS_INSTRUCTIONS", "").strip()
    if instructions:
        kwargs["instructions"] = instructions
    return openai.TTS(**kwargs)


def _fetch_interview_context(user_id: int) -> dict[str, Any]:
    base_url = (os.getenv("LARAVEL_INTERNAL_API_URL") or "").strip().rstrip("/")
    secret = (os.getenv("LARAVEL_INTERNAL_API_SECRET") or "").strip()
    if base_url == "" or secret == "":
        return {}

    url = f"{base_url}/internal/users/{user_id}/interview-context"
    req = request.Request(
        url=url,
        method="GET",
        headers={
            "Accept": "application/json",
            "X-Internal-Token": secret,
        },
    )

    try:
        with urlopen_internal(req, timeout=5) as response:
            body = response.read().decode("utf-8")
            data = json.loads(body)
            if isinstance(data, dict):
                return data
    except (error.URLError, TimeoutError, json.JSONDecodeError):
        return {}

    return {}


async def entrypoint(ctx: JobContext):
    # Connect to the room
    await ctx.connect(auto_subscribe=AutoSubscribe.SUBSCRIBE_ALL)
    await ctx.wait_for_participant()

    interview_config = {**DEFAULT_INTERVIEW_CONFIG}
    md = ""
    metadata_user_id: int | None = None
    for rp in ctx.room.remote_participants.values():
        md = rp.metadata or ""
        break

    prompt_context_from_metadata: dict[str, Any] | None = None
    if isinstance(md, str) and md.strip():
        try:
            extra = json.loads(md)
            if isinstance(extra, dict):
                raw_prompt_context = extra.get("prompt_context")
                if isinstance(raw_prompt_context, dict):
                    prompt_context_from_metadata = raw_prompt_context
                interview_config["job_role"] = extra.get(
                    "job_role", interview_config["job_role"]
                )
                interview_config["interview_type"] = extra.get(
                    "interview_type", interview_config["interview_type"]
                )
                interview_mode = extra.get("interview_mode")
                if isinstance(interview_mode, str) and interview_mode.strip():
                    interview_config["interview_mode"] = interview_mode.strip().lower()
                question_count = extra.get("question_count")
                if isinstance(question_count, int):
                    interview_config["question_count"] = question_count
                concise_feedback = extra.get("concise_feedback")
                if isinstance(concise_feedback, bool):
                    interview_config["concise_feedback"] = concise_feedback
                elif isinstance(concise_feedback, str):
                    interview_config["concise_feedback"] = concise_feedback.strip().lower() in {
                        "1",
                        "true",
                        "yes",
                        "on",
                    }
                cn = extra.get("candidate_name")
                if isinstance(cn, str) and cn.strip():
                    interview_config["candidate_name"] = cn.strip()
                direct_context_notes = extra.get("context_notes")
                if isinstance(direct_context_notes, str) and direct_context_notes.strip():
                    interview_config["context_notes"] = direct_context_notes.strip()
                raw_user_id = extra.get("user_id")
                if isinstance(raw_user_id, int):
                    metadata_user_id = raw_user_id
        except json.JSONDecodeError:
            pass

    if isinstance(prompt_context_from_metadata, dict):
        interview = prompt_context_from_metadata.get("interview")
        if isinstance(interview, dict):
            mode_value = interview.get("mode")
            if isinstance(mode_value, str) and mode_value.strip():
                interview_config["interview_mode"] = mode_value.strip().lower()
            type_value = interview.get("type")
            if isinstance(type_value, str) and type_value.strip():
                interview_config["interview_type"] = type_value.strip()
            type_context = interview.get("type_context")
            if isinstance(type_context, str) and type_context.strip():
                interview_config["interview_type_context"] = type_context.strip()

        session_meta = prompt_context_from_metadata.get("session")
        if isinstance(session_meta, dict):
            question_count = session_meta.get("question_count")
            if isinstance(question_count, int):
                interview_config["question_count"] = question_count
            concise_feedback = session_meta.get("concise_feedback")
            if isinstance(concise_feedback, bool):
                interview_config["concise_feedback"] = concise_feedback

        candidate = prompt_context_from_metadata.get("candidate")
        if isinstance(candidate, dict):
            candidate_name = candidate.get("name")
            if isinstance(candidate_name, str) and candidate_name.strip():
                interview_config["candidate_name"] = candidate_name.strip()
            candidate_job_role = candidate.get("job_role")
            if isinstance(candidate_job_role, str) and candidate_job_role.strip():
                interview_config["job_role"] = candidate_job_role.strip()

        instructions = prompt_context_from_metadata.get("instructions")
        if isinstance(instructions, dict):
            context_notes = instructions.get("context_notes")
            if isinstance(context_notes, str) and context_notes.strip():
                interview_config["context_notes"] = context_notes.strip()

    # If metadata omitted, fall back to LiveKit participant name / identity
    if not str(interview_config.get("candidate_name", "")).strip():
        for rp in ctx.room.remote_participants.values():
            fallback = (getattr(rp, "name", None) or rp.identity or "").strip()
            if fallback:
                interview_config["candidate_name"] = fallback
            break

    if metadata_user_id is not None and not str(interview_config.get("context_notes", "")).strip():
        context = _fetch_interview_context(metadata_user_id)
        context_notes = context.get("context_notes")
        if isinstance(context_notes, str) and context_notes.strip():
            interview_config["context_notes"] = context_notes.strip()

    openai_key = os.getenv("OPENAI_API_KEY")

    # Initialize the session with VAD, STT, LLM, and TTS
    # This unified session replaces VoicePipelineAgent and MultimodalAgent
    session = AgentSession(
        vad=_silero_vad(),
        stt=_openai_stt(openai_key or ""),
        llm=openai.LLM(
            model=os.getenv("OPENAI_LLM_MODEL", "gpt-4o-mini"),
            api_key=openai_key,
        ),
        tts=_openai_tts(openai_key or ""),
    )

    # Create the agent with interviewer instructions (no car-service tools)
    agent = Agent(
        instructions=build_system_prompt(interview_config),
        tools=[],
    )

    # Start the session
    await session.start(agent=agent, room=ctx.room)

    # Generate an opening greeting
    await session.generate_reply(instructions=WELCOME_MESSAGE)

if __name__ == "__main__":
    cli.run_app(WorkerOptions(entrypoint_fnc=entrypoint))