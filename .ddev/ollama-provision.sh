#!/bin/sh
# ollama-provision.sh — payload of the DDEV post-start hook
# (wired up in .ddev/config.yaml, service network member "ollama").
#
# Runs in the DDEV web container on every `ddev start` / `ddev restart`:
#   1. Waits for the DDEV "ollama" service to answer GET /api/version.
#   2. Checks whether the configured Qwen3 model is present via GET /api/tags.
#   3. Pulls it via POST /api/pull when it is missing.
#
# Idempotent: if the model already exists, the script exits almost
# immediately. Nothing here pulls repeatedly.
set -u

BASE="${AI_BRAIN_URL:-http://ollama:11434}"
MODEL="${AI_BRAIN_MODEL:-qwen3:4b}"

echo "[ollama-provision] Waiting for Ollama at $BASE..."

i=0
up=0
while [ "$i" -lt 60 ]; do
    if curl -sf --max-time 3 "$BASE/api/version" >/dev/null 2>&1; then
        up=1
        break
    fi
    i=$((i + 1))
    sleep 2
done

if [ "$up" -ne 1 ]; then
    echo "[ollama-provision] WARNING: Ollama did not answer at $BASE after about 2 minutes."
    echo "[ollama-provision] Check 'ddev logs -s web' and Docker Desktop; the Ollama service may not be healthy."
    exit 0
fi

echo "[ollama-provision] Ollama is up at $BASE."

if curl -sf --max-time 10 "$BASE/api/tags" 2>/dev/null | grep -qF "\"$MODEL\""; then
    echo "[ollama-provision] Model '$MODEL' is already present. Nothing to do."
    exit 0
fi

echo "[ollama-provision] Model '$MODEL' is missing; pulling it (first pull downloads ~2.5 GB for qwen3:4b)..."

if curl -sf --max-time 3600 -X POST "$BASE/api/pull" \
    -H 'Content-Type: application/json' \
    -d "{\"name\":\"$MODEL\",\"stream\":false}" >/dev/null; then
    echo "[ollama-provision] Pull command completed."
else
    echo "[ollama-provision] WARNING: pull of '$MODEL' from $BASE did not complete."
    echo "[ollama-provision] Check the Ollama container logs and network access; run 'ddev start' again to retry."
    exit 0
fi

if curl -sf --max-time 10 "$BASE/api/tags" 2>/dev/null | grep -qF "\"$MODEL\""; then
    echo "[ollama-provision] Model '$MODEL' is now present."
else
    echo "[ollama-provision] WARNING: '$MODEL' is still not listed by /api/tags after the pull attempt."
fi

exit 0
