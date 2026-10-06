ARG OLLAMA_VERSION=0.35.1
FROM ollama/ollama:${OLLAMA_VERSION}

ENV OLLAMA_MODEL=qwen3:4b \
    OLLAMA_HOST=0.0.0.0:11434

EXPOSE 11434

RUN <<'EOF'
cat > /usr/local/bin/entrypoint.sh <<'SCRIPT'
#!/bin/sh
set -eu

echo "Ollama started"

ollama serve &
server_pid=$!

trap 'kill "$server_pid" 2>/dev/null' TERM INT

until ollama list >/dev/null 2>&1; do
  if ! kill -0 "$server_pid" 2>/dev/null; then
    echo "Ollama server failed to start"
    exit 1
  fi
  sleep 1
done

echo "Checking model..."
if ollama show "$OLLAMA_MODEL" >/dev/null 2>&1; then
  echo "$OLLAMA_MODEL found"
else
  echo "Downloading $OLLAMA_MODEL..."
  ollama pull "$OLLAMA_MODEL"
  echo "$OLLAMA_MODEL found"
fi

echo "Ollama ready"

wait "$server_pid"
SCRIPT
chmod +x /usr/local/bin/entrypoint.sh
EOF

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
