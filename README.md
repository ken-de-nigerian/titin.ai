# VoiceFlow AI

AI-powered voice interview practice platform.

## Architecture

- **Frontend + Auth**: Laravel 11 + Vue 3 + Inertia (served by Laravel Herd at `voice-flow-ai.test`)
- **Voice Agent**: Python + LiveKit Agents SDK (Docker)
- **Token Server**: Python + Flask (Docker)

## Stack

- Laravel 11, Vue 3, Inertia.js, Tailwind CSS (Herd)
- Python 3.12, LiveKit Agents, OpenAI STT/TTS (Docker)
- LiveKit Server (Docker or LiveKit Cloud)

## Local development

### Prerequisites

- Laravel Herd
- Docker Desktop

### Start voice services

```bash
cp microservices/.env.example microservices/.env
# Fill in LIVEKIT_URL, LIVEKIT_API_KEY, LIVEKIT_API_SECRET, OPENAI_API_KEY
docker compose up
```

### Start Laravel

Herd automatically serves `voice-flow-ai.test`

### Run migrations

```bash
php artisan migrate
```

### Environment

Add to Laravel `.env`:

```env
VITE_LIVEKIT_URL=ws://localhost:7880
VITE_TOKEN_SERVER_URL=http://localhost:5001
```

## Routes

- `/` — Landing page
- `/dashboard` — Session history and stats (demo data)
- `/interview` — Live voice interview with AI
- `/feedback` — Post-interview feedback (demo data)

## Development

```bash
# Frontend dev server (Vite HMR)
npm run dev

# Python services (Docker)
docker compose up

# Laravel logs
php artisan pail
```

## Notes

- The Python microservice in `microservices/` is NOT modified — only moved from `backend/`
- Docker runs ONLY the Python token server + agent (no frontend container)
- ALL visual design from the React app is preserved exactly
- Auth is skipped for now — all routes are public
- The `microservices/.env` file is separate from Laravel's `.env`
- The Vite proxy in Laravel's `vite.config.ts` handles `/api` → `http://localhost:5001` for local dev
