from __future__ import annotations

import asyncio
import json
import logging
import os
import time
from typing import Any
from urllib import error, request

from dotenv import load_dotenv

from internal_http import urlopen_internal

from livekit import rtc
from livekit.agents import (
    Agent,
    AgentSession,
    AutoSubscribe,
    JobContext,
    WorkerOptions,
    cli,
    function_tool,
)
from livekit.plugins import cartesia, deepgram, openai, silero
from prompts import DEFAULT_INTERVIEW_CONFIG, WELCOME_MESSAGE, build_system_prompt

load_dotenv()

logger = logging.getLogger(__name__)

INTERVIEW_PROGRESS_TOPIC = "interview_progress"

_CANDIDATE_PARTICIPANT_KINDS: list[rtc.ParticipantKind.ValueType] = [
    rtc.ParticipantKind.PARTICIPANT_KIND_STANDARD,
]


def _coerce_positive_int(value: object) -> int | None:
    if isinstance(value, int) and value > 0:
        return value
    if isinstance(value, str) and value.strip().isdigit():
        parsed = int(value.strip())
        return parsed if parsed > 0 else None

    return None


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


def _deepgram_stt() -> deepgram.STT:
    """
    Streaming STT via LiveKit Deepgram plugin.
    See: https://docs.livekit.io/agents/models/stt/deepgram/
    """
    api_key = (os.getenv("DEEPGRAM_API_KEY") or "").strip()
    if api_key == "":
        raise RuntimeError("DEEPGRAM_API_KEY is not set.")

    model = (os.getenv("DEEPGRAM_STT_MODEL") or "nova-3").strip()
    language = (os.getenv("DEEPGRAM_STT_LANGUAGE") or "en-US").strip()
    sample_rate = int(_env_float("DEEPGRAM_STT_SAMPLE_RATE", 16000))

    kwargs: dict[str, Any] = {
        "api_key": api_key,
        "model": model,
        "language": language,
        "sample_rate": sample_rate,
    }

    keyterms_raw = (os.getenv("DEEPGRAM_STT_KEYTERMS") or "").strip()
    if keyterms_raw:
        parts = [p.strip() for p in keyterms_raw.split(",") if p.strip()]
        if parts:
            kwargs["keyterm"] = parts

    return deepgram.STT(**kwargs)


def _cartesia_tts() -> cartesia.TTS:
    """
    Low-latency TTS via LiveKit Cartesia plugin.
    See: https://docs.livekit.io/agents/models/tts/cartesia/
    """
    api_key = (os.getenv("CARTESIA_API_KEY") or "").strip()
    if api_key == "":
        raise RuntimeError("CARTESIA_API_KEY is not set.")

    model = (os.getenv("CARTESIA_TTS_MODEL") or "sonic-3").strip()
    language = (os.getenv("CARTESIA_TTS_LANGUAGE") or "en").strip()
    voice_raw = (os.getenv("CARTESIA_TTS_VOICE") or "").strip()

    kwargs: dict[str, Any] = {
        "api_key": api_key,
        "model": model,
        "language": language,
        "encoding": "pcm_s16le",
        "sample_rate": int(_env_float("CARTESIA_TTS_SAMPLE_RATE", 24000)),
    }

    if voice_raw:
        kwargs["voice"] = voice_raw

    speed = _env_optional_float("CARTESIA_TTS_SPEED")
    if speed is not None:
        kwargs["speed"] = speed

    return cartesia.TTS(**kwargs)


def _select_stt(openai_key: str) -> Any:
    if (os.getenv("DEEPGRAM_API_KEY") or "").strip():
        return _deepgram_stt()

    return _openai_stt(openai_key or "")


def _force_openai_tts() -> bool:
    return (os.getenv("FORCE_OPENAI_TTS") or "").strip().lower() in ("1", "true", "yes", "on")


def _select_tts(openai_key: str) -> Any:
    if not _force_openai_tts() and (os.getenv("CARTESIA_API_KEY") or "").strip():
        return _cartesia_tts()

    return _openai_tts(openai_key or "")


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


async def _publish_interview_progress(
    room: Any,
    *,
    questions_asked: int,
    questions_total: int,
    planned_duration_seconds: int,
) -> None:
    participant = getattr(room, "local_participant", None)
    if participant is None:
        return

    envelope = json.dumps(
        {
            "type": "interview_progress",
            "questions_asked": questions_asked,
            "questions_total": questions_total,
            "planned_duration_seconds": planned_duration_seconds,
        }
    )

    try:
        await participant.publish_data(
            envelope,
            reliable=True,
            topic=INTERVIEW_PROGRESS_TOPIC,
        )
    except Exception:
        logger.debug("interview progress publish failed", exc_info=True)


class InterviewerAgent(Agent):
    """
    Interviewer with monotonic primary-question milestones via register_primary_question,
    plus silent pacing hints via get_session_pacing.
    """

    def __init__(
        self,
        *,
        instructions: str,
        room: Any,
        questions_total: int,
        planned_duration_seconds: int,
    ) -> None:
        self._progress_room = room
        self._questions_total = max(3, min(20, int(questions_total)))
        self._planned_duration_seconds = max(60, int(planned_duration_seconds))
        self._next_expected_primary_index = 1
        self._session_started_at_monotonic: float | None = None
        super().__init__(instructions=instructions)

    def mark_session_started(self) -> None:
        """Anchor the pacing clock once the AgentSession is running (do not call before session.start)."""
        self._session_started_at_monotonic = time.monotonic()

    @function_tool()
    async def get_session_pacing(self) -> dict[str, Any]:
        """Return elapsed time vs the planned session length for silent pacing decisions only.

        Use before shifting to wrap-up, tightening follow-ups, or checking whether you have time
        for another primary question. Never read these numbers aloud to the candidate.

        Returns pacing_phase: opening | mid | wrap_up (heuristic based on elapsed fraction).
        """
        if self._session_started_at_monotonic is None:
            return {
                "available": False,
                "message": "Pacing clock not ready yet; rely on the planned minutes in your instructions.",
            }

        elapsed = max(0.0, time.monotonic() - self._session_started_at_monotonic)
        elapsed_s = int(elapsed)
        planned = self._planned_duration_seconds
        remaining = max(0, planned - elapsed_s)
        fraction = (elapsed_s / planned) if planned > 0 else 0.0

        if fraction < 0.12:
            phase = "opening"
        elif fraction > 0.82:
            phase = "wrap_up"
        else:
            phase = "mid"

        return {
            "available": True,
            "elapsed_seconds": elapsed_s,
            "planned_duration_seconds": planned,
            "remaining_seconds": remaining,
            "approx_minutes_elapsed": round(elapsed_s / 60.0, 1),
            "approx_minutes_remaining": round(remaining / 60.0, 1),
            "pacing_phase": phase,
            "primary_questions_total": self._questions_total,
            "next_primary_index_to_register": self._next_expected_primary_index,
        }

    @function_tool()
    async def register_primary_question(
        self,
        primary_question_index: int,
        topic_hint: str = "",
    ) -> dict[str, Any]:
        """Register a new PRIMARY interview question before you ask it aloud.

        Call once per new main topic (not for short follow-ups). Indices are 1-based and must
        increase by one each time: first primary is 1, then 2, then 3, etc.

        Args:
            primary_question_index: The 1-based index of this primary question (must equal the
                next required index).
            topic_hint: Optional few-word label for logging (omit if unsure).
        """
        try:
            idx = int(primary_question_index)
        except (TypeError, ValueError):
            return {
                "accepted": False,
                "message": "primary_question_index must be an integer.",
                "next_required_index": self._next_expected_primary_index,
            }

        if idx != self._next_expected_primary_index:
            logger.info(
                "register_primary_question rejected: expected %s got %s hint=%r",
                self._next_expected_primary_index,
                idx,
                (topic_hint or "")[:80],
            )

            return {
                "accepted": False,
                "message": (
                    f"Expected primary_question_index={self._next_expected_primary_index} "
                    f"next; you sent {idx}. Call again with the required index before speaking."
                ),
                "next_required_index": self._next_expected_primary_index,
            }

        if idx < 1 or idx > self._questions_total:
            logger.info(
                "register_primary_question rejected: index %s out of range 1..%s",
                idx,
                self._questions_total,
            )

            return {
                "accepted": False,
                "message": (
                    f"primary_question_index must be between 1 and {self._questions_total} "
                    "for this session."
                ),
                "next_required_index": self._next_expected_primary_index,
            }

        self._next_expected_primary_index = idx + 1
        hinted = topic_hint.strip()[:120] if isinstance(topic_hint, str) else ""

        logger.info(
            "register_primary_question accepted index=%s/%s hint=%r",
            idx,
            self._questions_total,
            hinted or None,
        )

        asyncio.create_task(
            _publish_interview_progress(
                self._progress_room,
                questions_asked=idx,
                questions_total=self._questions_total,
                planned_duration_seconds=self._planned_duration_seconds,
            )
        )

        remaining = max(0, self._questions_total - idx)

        return {
            "accepted": True,
            "questions_asked": idx,
            "questions_total": self._questions_total,
            "questions_remaining": remaining,
            "message": (
                "Progress recorded. Speak your primary interview question aloud now (spoken words "
                f"only — never mention tooling). Next milestone index after this topic: {idx + 1}."
            ),
        }


async def entrypoint(ctx: JobContext):
    """LiveKit Agents job entrypoint (see JobContext / AgentSession in livekit-agents).

    We connect explicitly with SUBSCRIBE_ALL, then wait for a **STANDARD** remote participant
    only. The SDK default wait_for_participant kinds include CONNECTOR and SIP, which can
    match before the browser candidate and yield the wrong metadata.
    """
    await ctx.connect(auto_subscribe=AutoSubscribe.SUBSCRIBE_ALL)
    try:
        candidate_participant = await ctx.wait_for_participant(kind=_CANDIDATE_PARTICIPANT_KINDS)
    except RuntimeError as exc:
        msg = str(exc).lower()
        if "disconnected" in msg and "waiting for participant" in msg:
            logger.warning(
                "Room ended before participant joined (job=%s): %s",
                getattr(getattr(ctx, "job", None), "id", None),
                exc,
            )
            return

        raise

    interview_config = {**DEFAULT_INTERVIEW_CONFIG}
    md = str(getattr(candidate_participant, "metadata", None) or "")
    metadata_user_id: int | None = None

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
                planned_seconds = extra.get("planned_duration_seconds")
                if isinstance(planned_seconds, int) and planned_seconds >= 60:
                    interview_config["planned_duration_seconds"] = planned_seconds
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
                coerced = _coerce_positive_int(raw_user_id)
                if coerced is not None:
                    metadata_user_id = coerced
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
            planned_seconds = session_meta.get("planned_duration_seconds")
            if isinstance(planned_seconds, int) and planned_seconds >= 60:
                interview_config["planned_duration_seconds"] = planned_seconds
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

    # If metadata omitted, fall back to the joined candidate's display name / identity
    if not str(interview_config.get("candidate_name", "")).strip():
        fallback = (
            getattr(candidate_participant, "name", None)
            or getattr(candidate_participant, "identity", None)
            or ""
        ).strip()
        if fallback:
            interview_config["candidate_name"] = fallback

    if metadata_user_id is not None and not str(interview_config.get("context_notes", "")).strip():
        context = _fetch_interview_context(metadata_user_id)
        context_notes = context.get("context_notes")
        if isinstance(context_notes, str) and context_notes.strip():
            interview_config["context_notes"] = context_notes.strip()

    openai_key = (os.getenv("OPENAI_API_KEY") or "").strip()
    if openai_key == "":
        logger.error(
            "OPENAI_API_KEY is not set; the interviewer LLM cannot start (job=%s).",
            getattr(getattr(ctx, "job", None), "id", None),
        )
        return

    questions_total = max(3, min(20, int(interview_config.get("question_count") or 6)))
    planned_duration_seconds = max(
        60, int(interview_config.get("planned_duration_seconds") or (25 * 60))
    )
    interview_config["question_count"] = questions_total
    interview_config["planned_duration_seconds"] = planned_duration_seconds

    # Initialize the session with VAD, STT, LLM, and TTS
    # This unified session replaces VoicePipelineAgent and MultimodalAgent
    session = AgentSession(
        vad=_silero_vad(),
        stt=_select_stt(openai_key),
        llm=openai.LLM(
            model=os.getenv("OPENAI_LLM_MODEL", "gpt-4o-mini"),
            api_key=openai_key,
        ),
        tts=_select_tts(openai_key),
    )

    agent = InterviewerAgent(
        instructions=build_system_prompt(interview_config),
        room=ctx.room,
        questions_total=questions_total,
        planned_duration_seconds=planned_duration_seconds,
    )

    # Start the session
    await session.start(agent=agent, room=ctx.room)
    agent.mark_session_started()

    await _publish_interview_progress(
        ctx.room,
        questions_asked=0,
        questions_total=questions_total,
        planned_duration_seconds=planned_duration_seconds,
    )

    # Generate an opening greeting
    await session.generate_reply(instructions=WELCOME_MESSAGE)

if __name__ == "__main__":
    cli.run_app(WorkerOptions(entrypoint_fnc=entrypoint))