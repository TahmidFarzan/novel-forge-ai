# Testing

Novel Forge AI uses PHPUnit (via `php artisan test`) with two test suites, Unit
and Feature. The suite is configured in `src/phpunit.xml`.

## Running tests

Locally, from `src/`:

```sh
php artisan test
```

Or inside the Docker stack:

```sh
docker compose exec app php artisan test
```

Filter and target specific suites:

```sh
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
php artisan test --filter=NovelGenerationPipelineTest
php artisan test src/tests/Unit/PromptContextEncoderTest.php
```

The Composer alias `composer test` clears the config cache first and then runs
the suite.

## Test environment

`phpunit.xml` overrides the environment so tests are fast and isolated:

| Variable | Value |
| --- | --- |
| `APP_ENV` | `testing` |
| `DB_CONNECTION` | `sqlite` |
| `DB_DATABASE` | `:memory:` |
| `CACHE_STORE` | `array` |
| `SESSION_DRIVER` | `array` |
| `QUEUE_CONNECTION` | `sync` |
| `MAIL_MAILER` | `array` |

No external database, Redis instance, or running Ollama server is required.

## Test layout

```
src/tests/
├── TestCase.php                        Base test case
├── Concerns/
│   └── InteractsWithPromptSkeleton.php Reusable prompt assertions
├── Unit/
│   ├── PromptContextEncoderTest.php            Prompt context encoding
│   ├── HuggingFaceApiServiceResponseFormatTest.php  Response parsing
│   └── ExampleTest.php
└── Feature/
    ├── NovelGenerationPipelineTest.php          End-to-end generation pipeline
    ├── NovelGenerationStateTest.php             Generation state transitions
    ├── FoundationGenerationReliabilityTest.php  Foundation generation retries
    ├── FoundationPromptContractSyncTest.php     Prompt/contract consistency
    ├── AiPromptSyncSeederTest.php               Seeder expectations
    ├── RenameNovelGeneratorStagesMigrationTest.php
    ├── HuggingFaceApiServiceRequestTest.php
    └── ExampleTest.php
```

## AI-related tests

Tests that exercise generation use Laravel's HTTP fake (`Http::fake`) to stub the
chat-completions endpoint, so they assert request/response handling without
network access or a loaded model. Feature tests that touch the database use the
`RefreshDatabase` trait against the in-memory SQLite database and seed only the
records they need.

## What to check before committing

```sh
php artisan test
vendor/bin/pint --test
```

`laravel/pint` is available as a dev dependency for code style; run
`vendor/bin/pint` to fix style automatically.
