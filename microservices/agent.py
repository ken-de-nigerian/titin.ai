from __future__ import annotations

import json
import os
from typing import Any

from dotenv import load_dotenv

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


async def entrypoint(ctx: JobContext):
    # Connect to the room
    await ctx.connect(auto_subscribe=AutoSubscribe.SUBSCRIBE_ALL)
    await ctx.wait_for_participant()

    interview_config = {**DEFAULT_INTERVIEW_CONFIG}
    md = ""
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
                cn = extra.get("candidate_name")
                if isinstance(cn, str) and cn.strip():
                    interview_config["candidate_name"] = cn.strip()
        except json.JSONDecodeError:
            pass

    # If metadata omitted, fall back to LiveKit participant name / identity
    if not str(interview_config.get("candidate_name", "")).strip():
        for rp in ctx.room.remote_participants.values():
            fallback = (getattr(rp, "name", None) or rp.identity or "").strip()
            if fallback:
                interview_config["candidate_name"] = fallback
            break

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