FROM python:3.12-slim

ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1 \
    PIP_DISABLE_PIP_VERSION_CHECK=1

# System deps for audio/ML wheels used by livekit-agents + onnxruntime + sounddevice builds.
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    ffmpeg \
    libasound2 \
    libgomp1 \
    libportaudio2 \
    libsndfile1 \
    portaudio19-dev \
    pkg-config \
    curl \
  && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY backend/requirements-docker.txt /tmp/requirements-docker.txt
RUN python -m pip install --no-cache-dir -r /tmp/requirements-docker.txt

COPY backend/ /app/
