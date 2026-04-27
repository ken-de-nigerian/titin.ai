from __future__ import annotations
import os
from dotenv import load_dotenv

from livekit.agents import (
    AutoSubscribe,
    JobContext,
    WorkerOptions,
    cli,
    AgentSession,
    Agent
)
from livekit.plugins import openai, silero
from prompts import WELCOME_MESSAGE, build_system_prompt, DEFAULT_INTERVIEW_CONFIG

load_dotenv()

async def entrypoint(ctx: JobContext):
    # Connect to the room
    await ctx.connect(auto_subscribe=AutoSubscribe.SUBSCRIBE_ALL)
    await ctx.wait_for_participant()

    openai_key = os.getenv("OPENAI_API_KEY") 

    # Initialize the session with VAD, STT, LLM, and TTS
    # This unified session replaces VoicePipelineAgent and MultimodalAgent
    session = AgentSession(
        vad=silero.VAD.load(),
        stt=openai.STT(api_key=openai_key), # Add api_key here
        llm=openai.LLM(model="gpt-4o-mini", api_key=openai_key), # And here
        tts=openai.TTS(api_key=openai_key) # And here
    )

    # Create the agent with interviewer instructions (no car-service tools)
    agent = Agent(
        instructions=build_system_prompt(DEFAULT_INTERVIEW_CONFIG),
        tools=[]
    )

    # Start the session
    await session.start(agent=agent, room=ctx.room)

    # Generate an opening greeting
    await session.generate_reply(instructions=WELCOME_MESSAGE)

if __name__ == "__main__":
    cli.run_app(WorkerOptions(entrypoint_fnc=entrypoint))