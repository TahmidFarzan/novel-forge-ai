# Ollama Docker Setup

## Overview

Docker automatically handles Ollama installation, port configuration, and model setup for Qwen3 4B Q4.

## Setup

Navigate to the Ollama docker directory and start the container:

```bash
cd docker/ollama
docker compose up -d
```

## Configuration

The container uses the following environment variables (defined in `.env.example`):

- `OLLAMA_URL`: `http://localhost:11434`
- `OLLAMA_MODEL`: `qwen3:4b`
- `OLLAMA_PORT`: `11434`

Container name: `novel-forge-ai-ollama`

## After Startup

- Ollama service will be running and accessible at `http://localhost:11434`
- The Qwen3 4B Q4 model will be automatically downloaded on first startup
- The Laravel application can connect using the configured URL
