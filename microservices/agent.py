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


def _openai_tts(api_key: str) -> openai.TTS:
    """
    OpenAI TTS defaults (gpt-4o-mini-tts + mp3 over SSE) can sound choppy when streamed in
    small chunks. Prefer raw PCM; tune model/voice via env (see microservices/.env.example).
    """
    kwargs: dict[str, Any] = {
        "api_key": api_key,
        "model": os.getenv("OPENAI_TTS_MODEL", "gpt-4o-mini-tts"),
        "voice": os.getenv("OPENAI_TTS_VOICE", "ash"),
        "speed": float(os.getenv("OPENAI_TTS_SPEED", "1.0")),
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

    if isinstance(md, str) and md.strip():
        try:
            extra = json.loads(md)
            if isinstance(extra, dict):
                interview_config["job_role"] = extra.get(
                    "job_role", interview_config["job_role"]
                )
                interview_config["interview_type"] = extra.get(
                    "interview_type", interview_config["interview_type"]
                )
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
                raw_user_id = extra.get("user_id")
                if isinstance(raw_user_id, int):
                    metadata_user_id = raw_user_id
        except json.JSONDecodeError:
            pass

    # If metadata omitted, fall back to LiveKit participant name / identity
    if not str(interview_config.get("candidate_name", "")).strip():
        for rp in ctx.room.remote_participants.values():
            fallback = (getattr(rp, "name", None) or rp.identity or "").strip()
            if fallback:
                interview_config["candidate_name"] = fallback
            break

    if metadata_user_id is not None:
        context = _fetch_interview_context(metadata_user_id)
        context_notes = context.get("context_notes")
        if isinstance(context_notes, str) and context_notes.strip():
            interview_config["context_notes"] = context_notes.strip()

    openai_key = os.getenv("OPENAI_API_KEY")

    # Initialize the session with VAD, STT, LLM, and TTS
    # This unified session replaces VoicePipelineAgent and MultimodalAgent
    session = AgentSession(
        vad=silero.VAD.load(),
        stt=openai.STT(api_key=openai_key),
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