ARG OLLAMA_VERSION=0.35.1
FROM ollama/ollama:${OLLAMA_VERSION}

ENV OLLAMA_MODEL=qwen3:4b \
    OLLAMA_HOST=0.0.0.0:11434

EXPOSE 11434

RUN printf '%s\n' \
    '#!/bin/sh' \
    'set -eu' \
    '' \
    'echo "Starting Ollama server..."' \
    'ollama serve &' \
    'server_pid=$!' \
    '' \
    'trap "kill $server_pid 2>/dev/null" TERM INT' \
    '' \
    'until ollama list >/dev/null 2>&1; do' \
    '  if ! kill -0 "$server_pid" 2>/dev/null; then' \
    '    echo "Ollama server failed to start" >&2' \
    '    exit 1' \
    '  fi' \
    '  sleep 1' \
    'done' \
    '' \
    'echo "Ollama server is ready"' \
    '' \
    'if ollama show "$OLLAMA_MODEL" >/dev/null 2>&1; then' \
    '  echo "Model $OLLAMA_MODEL is already available"' \
    'else' \
    '  echo "Pulling model $OLLAMA_MODEL..."' \
    '  ollama pull "$OLLAMA_MODEL"' \
    'fi' \
    '' \
    'echo "Ollama is ready with model $OLLAMA_MODEL"' \
    '' \
    'wait "$server_pid"' \
    > /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
