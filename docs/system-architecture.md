# System Architecture

Novel Forge AI is a Laravel 13 application that generates novel foundations,
chapter plans, and chapter prose with a local AI model. The frontend is a Vue 3
single-page application delivered through Inertia.js.

## Runtime topology

```
Browser
   │  http://novel-forge-ai.localhost
   ▼
Nginx ─────────────► PHP-FPM (Laravel app)
   │  public/ static          │  AI generation request
   ▼                          ▼
public/build assets       Ollama server (qwen3:4b)
```

The Docker environment runs four containers on the `novel-forge-ai-network`
bridge network: Nginx, PHP-FPM, the queue worker, and Ollama. Development adds a
Vite HMR container. Nginx is the only public HTTP entrypoint: it serves the
application's `public/` directory (including the Vite production build in
`public/build`) and forwards PHP requests to `app:9000`. The application and
queue containers reach Ollama at `http://ollama:11434`.

## Request lifecycle

1. Nginx serves static files from `public/` and forwards everything else to
   PHP-FPM.
2. Laravel routes (`routes/web.php`) resolve the request. Authenticated
   back-office routes are grouped under `/back-office`.
3. Controllers delegate to services in `app/Services`.
4. Inertia renders a Vue page component with server-provided props. The response
   is a single HTML shell (`resources/views/app.blade.php`) plus a JSON payload.
5. On the client, Inertia swaps the page component without a full reload. Ziggy
   exposes Laravel's named routes to Vue via `window.Ziggy`.

## Frontend

- Entry point: `resources/js/app.js` creates the Inertia app and resolves pages
  from `resources/js/Pages/**/*.vue`.
- Pages are organised by area: `auth`, `auth-user`, `back-office`, and shared
  `layouts`.
- Styling uses Tailwind CSS 4 via the `@tailwindcss/vite` plugin.
- Vite is configured with the `@` alias pointing at `resources/js`.
- The production asset build is written to `public/build` and served statically
  by Nginx. In development, the `vite` Compose service runs `npm run dev`, serves
  assets over HMR, and writes `public/hot`.

## Domain model

| Model | Responsibility |
| --- | --- |
| `Novel` | The work being generated; holds foundation, characters, world bible, chapters |
| `NovelChapter` | A chapter belonging to a novel, including summary and content |
| `AiBrain` | A configured AI model (name, model tag, provider, endpoint, key, limits) |
| `AiBrainOutputType` | Output formats a brain supports (for example `Text`) |
| `AiPrompt` | Prompt templates for the generation steps |
| `NovelGeneratorStep` | Ordered generation pipeline steps |
| `Genre`, `Audience`, `NovelType`, `Language` | Story metadata used to steer prompts |
| `KdpLayout`, `DocumentStyle` | Publishing layout and formatting presets |
| `MediaUpload` | Media library records (Spatie Media Library) |
| `User`, `UserPermission` | Authentication and fine-grained permissions |

Relationships are cleaned up asynchronously by the `Delete*RelationsJob` jobs.

## AI generation architecture

The generation pipeline is driven by three named steps (constants in
`App\Helpers\AiPromptGeneratorHelper`):

1. `foundation` — builds the novel's foundation, characters, world bible, and
   related context.
2. `plan_chapter` — produces the chapter plan.
3. `chapter_content` — writes the chapter prose.

`NovelGeneratorService` and `NovelChapterService` orchestrate these steps and call
`App\Services\BackOffice\AiService`, which:

- merges the partial prompt with the step inputs,
- validates that no placeholder is left unresolved,
- builds a payload containing both `prompt` and `messages`,
- posts to the resolved endpoint,
- normalises the response into the format expected by the step.

### Endpoint resolution

`AiService` posts to the AI Brain's `api_url`. If the URL does not already end
with `/api/generate` or `/v1/chat/completions`, it appends `/api/generate`, the
native Ollama generation endpoint. Responses in Ollama's native shape
(`{ "response": ..., "done": ... }`) are normalised to the OpenAI
`choices[].message.content` shape, so both Ollama native and
OpenAI-compatible/HuggingFace providers work through the same code path.

Every AI Brain must have a non-empty `api_key`; `AiService` rejects requests
without one. Ollama ignores the key, so a placeholder value such as `ollama` is
used for local development.

### Model catalog

`src/config/ai-brain.php` defines the available brains. The default entry is
`qwen3:4b` with a 32768-token context window, a 600-second timeout, and a maximum
of 32000 output tokens. The endpoint is read from `AI_BRAIN_URL` (falling back to
`OLLAMA_URL`). Seeder `AiBrainSeeder` copies the catalog into the database, and
`AiBrainAiBrainOutputTypeSeeder` links each brain to its supported output types.
At runtime the database record is authoritative, with the config acting as a
default.

## Back office

`routes/web.php` exposes a `/back-office` area for CRUD management of genres,
audiences, novel types, languages, AI brains, prompts, KDP layouts, document
styles, media, users, activity logs, and the queue monitor. Super-admin-only
sections (users, queue monitor) are protected by the `IsSuperAdmin` middleware.

## Cross-cutting concerns

- **Search**: `SearchController` and `SearchService` provide lookup endpoints for
  select inputs, wrapped in the `response.cache` middleware with ETag support.
- **Activity logging**: `spatie/laravel-activitylog` records model changes.
- **Media**: `spatie/laravel-medialibrary` stores uploads on the `public` disk;
  media events are handled by listeners in `app/Listeners`.
- **Queues**: `romanzipp/laravel-queue-monitor` provides a dashboard for
  background jobs. The Docker stack runs synchronously by default; the manual
  `php artisan dev` command runs a queue listener.
- **Permissions**: `UserPermission` records and the `UserPermissionHelper`
  drive authorization in addition to policies in `app/Policies`.

## Directory map

```
src/
├── app/
│   ├── Http/            Controllers, middleware, form requests
│   ├── Services/        Business logic (BackOffice/*, SearchService, AuthService)
│   ├── Models/          Eloquent models
│   ├── Helpers/         Prompt generation, novel, seeder, system helpers
│   ├── Jobs/            Relationship cleanup jobs
│   ├── Events/ Listeners/
│   ├── Observers/ Policies/ Providers/
│   └── Support/         PromptContextEncoder
├── config/              Framework and app configuration (incl. ai-brain.php)
├── database/            Migrations, factories, seeders
├── resources/
│   ├── js/              Vue app, Inertia pages, components
│   ├── css/             Tailwind entry
│   └── views/           Blade shell (app.blade.php)
├── routes/              web.php, console.php
└── tests/               Unit and Feature suites
```
