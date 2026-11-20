# Testing

Novel Forge AI uses PHPUnit via `php artisan test`, with Unit and Feature
suites configured in `src/phpunit.xml`. Tests run inside the DDEV web container.

> **USER MUST EXECUTE MANUALLY.** The migration agent did not run the test
> suite.

## Running tests

From `src/` with DDEV:

```sh
ddev artisan test
```

Target a suite or a single test:

```sh
ddev artisan test --testsuite=Unit
ddev artisan test --testsuite=Feature
ddev artisan test --filter=NovelGenerationPipelineTest
ddev artisan test tests/Unit/PromptContextEncoderTest.php
```

The Composer alias clears the config cache first and then runs the suite:

```sh
ddev composer test
```

If you use the optional [manual installation](manual-installation.md), run the
same commands directly with `php artisan test`.

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

No external database, Redis instance, Memcached instance, or running Ollama
server is required.

## Test layout

```
src/tests/
├── TestCase.php                        Base test case
├── Concerns/
│   └── InteractsWithPromptSkeleton.php Reusable prompt assertions
├── Unit/
│   ├── PromptContextEncoderTest.php            Prompt context encoding
│   └── ExampleTest.php
└── Feature/
    ├── NovelGenerationPipelineTest.php          End-to-end generation pipeline
    ├── NovelGenerationStateTest.php             Generation state transitions
    ├── FoundationGenerationReliabilityTest.php  Foundation generation retries
    ├── FoundationPromptContractSyncTest.php     Prompt/contract consistency
    ├── AiPromptSyncSeederTest.php               Seeder expectations
    ├── RenameNovelGeneratorStagesMigrationTest.php
    └── ExampleTest.php
```

## AI-related tests

Tests that exercise generation use Laravel's HTTP fake (`Http::fake`) to stub the
chat-completions endpoint, so they assert request/response handling without
network access or a loaded model. Feature tests that touch the database use the
`RefreshDatabase` trait against the in-memory SQLite database and seed only the
records they need.

## Before committing

```sh
ddev artisan test
ddev exec vendor/bin/pint --test
```

`laravel/pint` is a dev dependency; run `ddev exec vendor/bin/pint` to fix style
automatically.

## Related documentation

- [Development environment](development-environment.md)
- [Command reference](command-reference.md)
- [Troubleshooting](troubleshooting.md)
