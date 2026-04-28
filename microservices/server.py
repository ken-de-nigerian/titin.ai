import json
import os
from livekit import api
from flask import Flask, request
from dotenv import load_dotenv
from flask_cors import CORS
from livekit.api import LiveKitAPI, ListRoomsRequest
import uuid

load_dotenv()

app = Flask(__name__)
CORS(app, resources={r"/*": {"origins": "*"}})

def _livekit_rest_url() -> str | None:
    """
    LiveKit has two relevant URLs:
    - WebRTC signaling URL used by clients/agents (often `wss://...`) via LIVEKIT_URL
    - Server REST API base URL used by LiveKitAPI (must be `http(s)://...`)

    We keep LIVEKIT_URL as-is for agents/clients, and derive a REST URL for API calls.
    You may also set LIVEKIT_REST_URL explicitly.
    """
    url = os.getenv("LIVEKIT_REST_URL") or os.getenv("LIVEKIT_URL")
    if not url:
        return None
    if url.startswith("wss://"):
        return "https://" + url.removeprefix("wss://")
    if url.startswith("ws://"):
        return "http://" + url.removeprefix("ws://")
    return url

async def generate_room_name():
    name = "room-" + str(uuid.uuid4())[:8]
    rooms = await get_rooms()
    while name in rooms:
        name = "room-" + str(uuid.uuid4())[:8]
    return name

async def get_rooms():
    rest_url = _livekit_rest_url()
    api = LiveKitAPI(rest_url)
    rooms = await api.room.list_rooms(ListRoomsRequest())
    await api.aclose()
    return [room.name for room in rooms.rooms]

@app.route("/getToken")
async def get_token():
    # Used for LiveKit identity and for the agent prompt (avoid default "my name" — LLMs repeat it oddly).
    name = (request.args.get("name") or "guest").strip() or "guest"
    room = request.args.get("room", None)
    job_role = request.args.get("job_role") or "Software Engineer"
    interview_type = request.args.get("interview_type") or "behavioral"

    if not room:
        room = await generate_room_name()

    participant_meta = json.dumps(
        {
            "job_role": job_role,
            "interview_type": interview_type,
            "candidate_name": name,
        }
    )

    token = (
        api.AccessToken(os.getenv("LIVEKIT_API_KEY"), os.getenv("LIVEKIT_API_SECRET"))
        .with_identity(name)
        .with_name(name)
        .with_metadata(participant_meta)
        .with_grants(
            api.VideoGrants(
                room_join=True,
                room=room,
            )
        )
    )

    return token.to_jwt()

if __name__ == "__main__":
    debug = os.getenv("FLASK_DEBUG", "false").lower() in ("1", "true", "yes")
    app.run(host="0.0.0.0", port=5001, debug=debug)