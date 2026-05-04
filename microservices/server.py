import json
import os
import re
from datetime import timedelta
import zipfile
from io import BytesIO
from xml.etree import ElementTree
from zipfile import BadZipFile
from urllib import request as urlrequest, error as urlerror
from urllib.error import HTTPError

try:
    from pypdf import PdfReader
    from pypdf.errors import PdfReadError
except ImportError:
    PdfReader = None  # type: ignore[misc, assignment]
    PdfReadError = ValueError  # type: ignore[misc, assignment]

from internal_http import urlopen_internal
from livekit import api
from flask import Flask, request, jsonify
from dotenv import load_dotenv
from flask_cors import CORS
from livekit.api import LiveKitAPI, ListRoomsRequest
import uuid

load_dotenv()

app = Flask(__name__)
_cors_origins = [
    origin.strip()
    for origin in (os.getenv("TOKEN_SERVER_CORS_ORIGINS") or "").split(",")
    if origin.strip()
]
if not _cors_origins:
    _cors_origins = [os.getenv("APP_URL", "http://localhost")]
CORS(app, resources={r"/*": {"origins": _cors_origins}})


def _cv_bounded_int(env_name: str, default: int, *, min_value: int, max_value: int) -> int:
    try:
        raw = (os.getenv(env_name) or "").strip()
        parsed = int(raw) if raw else default
    except ValueError:
        parsed = default
    return max(min_value, min(max_value, parsed))


def _cv_max_download_bytes() -> int:
    """
    Ceiling for raw file bytes fetched from signed URLs before parsing.

    Limits decompression/stream bombs and parser DoS risk (defense in depth with Laravel validation).
    """
    return _cv_bounded_int(
        "CV_MAX_DOWNLOAD_BYTES",
        10 * 1024 * 1024,
        min_value=512 * 1024,
        max_value=50 * 1024 * 1024,
    )


def _cv_pdf_max_pages() -> int:
    return _cv_bounded_int("CV_PDF_MAX_PAGES", 50, min_value=1, max_value=500)


def _cv_pdf_max_extract_chars() -> int:
    return _cv_bounded_int(
        "CV_PDF_MAX_EXTRACT_CHARS",
        500_000,
        min_value=5000,
        max_value=2_000_000,
    )


def _cv_docx_max_xml_bytes() -> int:
    """Max uncompressed bytes for ``word/document.xml`` inside DOCX ZIP."""
    return _cv_bounded_int(
        "CV_DOCX_MAX_XML_BYTES",
        8 * 1024 * 1024,
        min_value=128 * 1024,
        max_value=40 * 1024 * 1024,
    )


def _cv_txt_max_bytes() -> int:
    return _cv_bounded_int("CV_TXT_MAX_BYTES", 2 * 1024 * 1024, min_value=4096, max_value=20 * 1024 * 1024)


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

def _is_authorized_internal_call() -> bool:
    expected = (os.getenv("LIVEKIT_INTERNAL_TOKEN_SECRET") or "").strip()
    provided = (request.headers.get("X-Internal-Token") or "").strip()
    return expected != "" and provided == expected


def _post_callback(callback_url: str, payload: dict) -> None:
    token = (os.getenv("LIVEKIT_INTERNAL_TOKEN_SECRET") or "").strip()
    body = json.dumps(payload).encode("utf-8")
    req = urlrequest.Request(
        url=callback_url,
        data=body,
        method="POST",
        headers={
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-Internal-Token": token,
        },
    )
    with urlopen_internal(req, timeout=8):
        return


def _extract_pdf_text(body: bytes) -> str:
    """
    Text-only extraction. Does not execute PDF JavaScript or launch actions (pypdf has no JS engine).

    Mitigations: size cap, page cap, skip password-protected PDFs, strict stream limits, bounded output.
    """
    if PdfReader is None:
        return ""

    max_bytes = _cv_max_download_bytes()
    if len(body) > max_bytes:
        app.logger.warning("PDF rejected: %s bytes exceeds CV_MAX_DOWNLOAD_BYTES", len(body))
        return ""

    try:
        reader = PdfReader(BytesIO(body), strict=False)
    except (OSError, ValueError, PdfReadError):
        return ""

    if getattr(reader, "is_encrypted", False):
        app.logger.warning("PDF rejected: encrypted (no password support in parser)")
        return ""

    max_pages = _cv_pdf_max_pages()
    parts: list[str] = []
    total_chars = 0
    max_chars = _cv_pdf_max_extract_chars()

    try:
        for page in reader.pages[:max_pages]:
            extracted = page.extract_text() or ""
            if not extracted:
                continue
            if total_chars + len(extracted) > max_chars:
                allowed = max(0, max_chars - total_chars)
                if allowed:
                    parts.append(extracted[:allowed])
                break
            parts.append(extracted)
            total_chars += len(extracted)
    except (OSError, ValueError, UnicodeDecodeError, PdfReadError, TypeError):
        return ""

    return "\n".join(parts)


def _download_text(file_url: str, file_name: str) -> str:
    if file_url.strip() == "":
        return ""

    req = urlrequest.Request(url=file_url, method="GET")
    with urlopen_internal(req, timeout=10) as response:
        body = response.read()

    max_b = _cv_max_download_bytes()
    if len(body) > max_b:
        app.logger.warning("CV download rejected: %s bytes > ceiling %s", len(body), max_b)
        return ""

    lower_name = file_name.lower()
    if lower_name.endswith(".txt"):
        limit = min(len(body), _cv_txt_max_bytes())
        return body[:limit].decode("utf-8", errors="ignore")

    if lower_name.endswith(".docx"):
        try:
            with zipfile.ZipFile(BytesIO(body)) as archive:
                doc_path = "word/document.xml"
                if doc_path not in archive.namelist():
                    return ""
                meta = archive.getinfo(doc_path)
                if meta.file_size > _cv_docx_max_xml_bytes():
                    app.logger.warning(
                        "docx rejected: document.xml uncompressed %s bytes exceeds limit",
                        meta.file_size,
                    )
                    return ""
                xml = archive.read(doc_path)
        except (BadZipFile, OSError, RuntimeError, ValueError, zipfile.LargeZipFile):
            return ""

        try:
            root = ElementTree.fromstring(xml)
        except ElementTree.ParseError:
            return ""
        text_parts = [node.text for node in root.iter() if node.text]

        return " ".join(text_parts)

    if lower_name.endswith(".pdf"):
        decoded = _extract_pdf_text(body)
        if decoded.strip():
            return decoded[:50_000]
        ascii_fragments = re.findall(rb"[A-Za-z][A-Za-z0-9,\.\-\s]{20,}", body[: max_b])

        return " ".join(fragment.decode("utf-8", errors="ignore") for fragment in ascii_fragments[:40])

    return body.decode("utf-8", errors="ignore")


def _infer_skills(text: str) -> list[str]:
    skill_keywords = [
        "laravel",
        "php",
        "vue",
        "inertia",
        "typescript",
        "javascript",
        "python",
        "sql",
        "mysql",
        "postgres",
        "redis",
        "docker",
        "kubernetes",
        "aws",
        "api",
        "rest",
        "git",
        "tailwind",
        "livekit",
    ]
    lower = text.lower()
    return [skill for skill in skill_keywords if skill in lower][:8]


def _normalize_skill_label(value: str) -> str:
    token = value.strip().lower()
    synonym_map = {
        "js": "javascript",
        "ts": "typescript",
        "rest": "rest api",
        "restful api": "rest api",
        "communication skills": "communication",
    }
    normalized = synonym_map.get(token, token)
    return normalized[:80]


def _call_openai_cv_extractor(text: str) -> dict | None:
    api_key = (os.getenv("OPENAI_API_KEY") or "").strip()
    if api_key == "" or text.strip() == "":
        return None

    payload = {
        "model": os.getenv("OPENAI_CV_EXTRACT_MODEL", "gpt-4o-mini"),
        "temperature": 0,
        "response_format": {"type": "json_object"},
        "messages": [
            {
                "role": "system",
                "content": (
                    "Extract structured data from CV/resume plain text. Return strict JSON only. "
                    'Schema: {'
                    '"display_name": string,'
                    '"skills": string[],'
                    '"experience": [{"role": string, "company": string, "period": string, "summary": string}],'
                    '"professional_summary": string,'
                    '"domains": string[]'
                    "}. "
                    "display_name: candidate full name only (no job title appended). "
                    "experience: chronological most recent first; summary is 1-3 short clauses per role from the CV. "
                    "skills: technical and substantive professional skills mentioned. "
                    "professional_summary: 2-4 sentences capturing overall profile."
                ),
            },
            {
                "role": "user",
                "content": text[:24000],
            },
        ],
    }
    body = json.dumps(payload).encode("utf-8")
    req = urlrequest.Request(
        url="https://api.openai.com/v1/chat/completions",
        data=body,
        method="POST",
        headers={
            "Authorization": f"Bearer {api_key}",
            "Content-Type": "application/json",
        },
    )

    try:
        with urlrequest.urlopen(req, timeout=20) as response:
            raw = response.read().decode("utf-8")
    except (urlerror.URLError, TimeoutError, HTTPError):
        return None

    try:
        data = json.loads(raw)
        content = data["choices"][0]["message"]["content"]
        parsed = json.loads(content)
        if isinstance(parsed, dict):
            return parsed
    except (KeyError, IndexError, TypeError, json.JSONDecodeError):
        return None

    return None


def _cv_text_and_filename_for_skills(cv_text: str, file_label: str) -> str:
    trimmed = cv_text.strip()
    lower = file_label.lower().strip()
    stem = file_label.strip()
    for ext in (".pdf", ".docx", ".txt"):
        if lower.endswith(ext):
            stem = stem[: -len(ext)]
            stem = stem.replace("_", " ").replace("-", " ").strip()

    return "\n".join(segment for segment in (trimmed, file_label.strip(), stem) if segment).strip()


def _normalize_llm_skill_list(raw: object) -> list[str]:
    if not isinstance(raw, list):
        return []
    normalized: list[str] = []
    seen: set[str] = set()
    for skill in raw:
        if not isinstance(skill, str):
            continue
        label = skill.replace("\\/", "/").strip()
        label = _normalize_skill_label(label)
        if label == "" or label in seen:
            continue
        seen.add(label)
        normalized.append(label[:80])
    return normalized[:24]


def _skills_and_mode_with_llm(
    llm: dict | None, combined_source: str, mode: str
) -> tuple[list[str], str]:
    heuristic_skills = _infer_skills(combined_source if combined_source else "")
    if mode == "heuristic":
        return heuristic_skills, "heuristic"

    if llm is not None:
        normalized = _normalize_llm_skill_list(llm.get("skills"))
        if normalized:
            return normalized, "llm"

    if mode == "llm":
        return heuristic_skills, "llm_fallback_heuristic"

    return heuristic_skills, "heuristic_fallback"


def _sanitize_experience(entries: object) -> list[dict]:
    if not isinstance(entries, list):
        return []
    rows: list[dict] = []
    for raw in entries[:25]:
        if not isinstance(raw, dict):
            continue
        role = str(raw.get("role") or "").strip()
        company = str(raw.get("company") or "").strip()
        period = str(raw.get("period") or raw.get("dates") or "").strip()
        summary = str(raw.get("summary") or raw.get("highlights") or "").strip()
        if role == "" and company == "" and summary == "":
            continue
        rows.append(
            {
                "role": role[:200],
                "company": company[:200],
                "period": period[:120],
                "summary": summary[:800],
            }
        )
    return rows


def _raw_profile_excerpt_max_chars() -> int:
    try:
        parsed = int((os.getenv("CV_PROFILE_RAW_EXCERPT_CHARS") or "").strip() or "12000")
        return max(2000, min(100_000, parsed))
    except ValueError:
        return 12_000


def _participant_access_token_ttl() -> timedelta:
    """
    LiveKit JWT validity for the browser participant.
    Default 8h so long practice sessions are not cut off by short token TTLs.
    """
    raw = (os.getenv("LIVEKIT_PARTICIPANT_TOKEN_TTL_HOURS") or "").strip()
    try:
        hours = int(raw) if raw else 8
    except ValueError:
        hours = 8
    hours = max(1, min(24, hours))
    return timedelta(hours=hours)


@app.route("/internal/issue-token", methods=["POST"])
async def issue_token_internal():
    if not _is_authorized_internal_call():
        return jsonify({"message": "Unauthorized"}), 401

    payload = request.get_json(silent=True) or {}

    user_id = payload.get("user_id")
    if not isinstance(user_id, int):
        return jsonify({"message": "user_id (int) is required"}), 422

    # Human-readable name for room participant display.
    name = str(payload.get("name") or f"user_{user_id}").strip() or f"user_{user_id}"
    # Stable identity consumed by the agent.
    identity = f"user_{user_id}"
    room = payload.get("room")
    interview_mode = str(payload.get("interview_mode") or "simulation")
    job_role = str(payload.get("job_role") or "Software Engineer")
    interview_type = str(payload.get("interview_type") or "behavioral")
    email = str(payload.get("email") or "")
    concise_feedback = bool(payload.get("concise_feedback", False))
    question_count = int(payload.get("question_count") or 6)
    planned_duration_seconds = int(payload.get("planned_duration_seconds") or (25 * 60))
    context_notes = str(payload.get("context_notes") or "")
    raw_prompt_context = payload.get("prompt_context")
    prompt_context = raw_prompt_context if isinstance(raw_prompt_context, dict) else {}

    if not room:
        room = await generate_room_name()

    participant_meta = json.dumps(
        {
            "user_id": user_id,
            "email": email,
            "interview_mode": interview_mode,
            "job_role": job_role,
            "interview_type": interview_type,
            "question_count": question_count,
            "planned_duration_seconds": planned_duration_seconds,
            "candidate_name": name,
            "concise_feedback": concise_feedback,
            "context_notes": context_notes,
            "prompt_context": prompt_context,
        }
    )

    token = (
        api.AccessToken(os.getenv("LIVEKIT_API_KEY"), os.getenv("LIVEKIT_API_SECRET"))
        .with_identity(identity)
        .with_name(name)
        .with_metadata(participant_meta)
        .with_ttl(_participant_access_token_ttl())
        .with_grants(
            api.VideoGrants(
                room_join=True,
                room=room,
            )
        )
    )

    return jsonify({"token": token.to_jwt(), "room": room})


@app.route("/internal/cv/parse", methods=["POST"])
def parse_cv_internal():
    if not _is_authorized_internal_call():
        return jsonify({"message": "Unauthorized"}), 401

    payload = request.get_json(silent=True) or {}
    user_id = payload.get("user_id")
    cv_id = payload.get("cv_id")
    callback_url = str(payload.get("callback_url") or "").strip()
    file_url = str(payload.get("file_url") or "").strip()
    file_name = str(payload.get("file_name") or "resume")

    if not isinstance(user_id, int) or not isinstance(cv_id, int) or callback_url == "":
        return jsonify({"message": "user_id, cv_id and callback_url are required"}), 422

    try:
        downloaded_text = _download_text(file_url, file_name)
    except HTTPError as download_error:
        app.logger.warning("CV download failed: HTTP %s for %s", download_error.code, file_url)

        return (
            jsonify(
                {
                    "message": "CV download failed",
                    "status": download_error.code,
                    "hint": "Check PYTHON_ACCESSIBLE_APP_URL (Laravel) + Docker extra_hosts for your site hostname; see docker-compose.yml",
                }
            ),
            502,
        )
    except urlerror.URLError as download_error:
        app.logger.warning("CV download URLError for %s: %s", file_url, download_error)

        return (
            jsonify(
                {
                    "message": "CV download failed (network)",
                    "hint": "Container cannot reach Laravel; verify host URL and SSL settings (LARAVEL_INTERNAL_SSL_INSECURE for local Herd)",
                }
            ),
            502,
        )
    base_name = file_name.rsplit(".", 1)[0].replace("_", " ").replace("-", " ").strip()
    combined_source = _cv_text_and_filename_for_skills(downloaded_text, file_name)

    cv_mode = (os.getenv("CV_SKILL_EXTRACTION_MODE") or "hybrid").strip().lower()
    if cv_mode not in {"heuristic", "llm", "hybrid"}:
        cv_mode = "hybrid"

    llm_profile: dict | None = None
    if cv_mode in {"llm", "hybrid"}:
        llm_profile = _call_openai_cv_extractor(combined_source or downloaded_text)

    extracted_skills, extraction_mode = _skills_and_mode_with_llm(
        llm_profile, combined_source, cv_mode,
    )

    display_name = ""
    professional_summary = ""
    domains_list: list[str] = []
    experience_rows: list[dict] = []
    if llm_profile is not None:
        dn = llm_profile.get("display_name")
        if isinstance(dn, str):
            display_name = dn.strip()[:240]
        ps_raw = llm_profile.get("professional_summary") or llm_profile.get("summary")
        if isinstance(ps_raw, str):
            professional_summary = ps_raw.strip()[:6000]
        domains_raw = llm_profile.get("domains")
        if isinstance(domains_raw, list):
            domains_list = [
                str(entry).strip()[:120]
                for entry in domains_raw[:12]
                if isinstance(entry, str) and str(entry).strip()
            ]
        experience_rows = _sanitize_experience(llm_profile.get("experience"))

    profile_name = display_name or base_name or f"user {user_id}"
    excerpt_limit = _raw_profile_excerpt_max_chars()

    profile: dict[str, object] = {
        "name": profile_name,
        "skills": extracted_skills,
        "experience": experience_rows,
        "summary": professional_summary or "",
        "domains": domains_list,
        "source": "hybrid_parser_v1",
        "skill_extraction_mode": extraction_mode,
        "raw_text_excerpt": downloaded_text[:excerpt_limit],
    }

    callback_payload = {
        "cv_id": cv_id,
        "user_id": user_id,
        "status": "parsed",
        "schema_version": "v1",
        "profile": profile,
    }

    try:
        _post_callback(callback_url, callback_payload)
    except (urlerror.URLError, TimeoutError):
        return jsonify({"message": "Callback failed"}), 502

    return jsonify({"message": "CV parsed", "cv_id": cv_id})


@app.route("/health", methods=["GET"])
def health():
    return jsonify({"ok": True})

if __name__ == "__main__":
    debug = os.getenv("FLASK_DEBUG", "false").lower() in ("1", "true", "yes")
    app.run(host="0.0.0.0", port=5001, debug=debug)