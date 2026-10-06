# Installation Guide

## System Requirements

Refer to [Introduction](introduction.md) for complete system requirements.

## Installation Steps

### 1. Clone Project

```bash
git clone <repository-url>
cd novel-forge-ai/src
```

### 2. Environment Setup

Copy the environment file:

```bash
cp .env.example .env
```

### 3. Install Backend Dependencies

If `vendor/` does not exist, install dependencies:

```bash
composer install
```

Composer installs all Laravel PHP dependencies.

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Database Migration

```bash
php artisan migrate
```

### 6. Storage Link

Laravel requires a symbolic link from `public/storage` to `storage/app/public`.

Check if `public/storage` exists:

- If it does not exist, run: `php artisan storage:link`
- If it exists but is broken, remove it first: `rm public/storage` (or `rmdir`/delete), then run: `php artisan storage:link`

### 7. Install Frontend Dependencies

If `node_modules/` does not exist:

```bash
npm install
```

**Development:**

```bash
npm run dev
```

**Production:**

```bash
npm run build
```

### 8. Run Laravel Server

```bash
php artisan serve
```

## Development Workflow

Two services need to run for development:

**Terminal 1 - Ollama:**
- Manual: `ollama serve` (see [Ollama Manual Setup](ollama-manual.md))
- Docker: `docker compose up -d` (see [Ollama Docker Setup](ollama-docker.md))

**Terminal 2 - Laravel:**
```bash
php artisan serve
```

**Terminal 3 - Frontend:**
```bash
npm run dev
```
