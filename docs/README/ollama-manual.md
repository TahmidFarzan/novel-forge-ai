# Ollama Manual Setup

## Install Ollama

Download and install Ollama from the [official website](https://ollama.com/download).

Verify installation:

```bash
ollama --version
```

## Start Ollama

Start the Ollama server:

```bash
ollama serve
```

## Download Model

Pull the Qwen3 4B Q4 model:

```bash
ollama pull qwen3:4b
```

## Verify Model

Check that the model is installed:

```bash
ollama list
```

## Connect to Application

The Laravel application connects to the running Ollama server. By default, the configuration uses:

- URL: `http://localhost:11434`
- Model: `qwen3:4b`

These values are managed through the AI Brain configuration in the application.
