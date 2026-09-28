<?php

namespace Tests\Feature;

use App\Helpers\AiPromptGeneratorHelper;
use App\Helpers\NovelHelper;
use App\Models\Novel;
use App\Models\NovelGeneratorStep;
use App\Models\User;
use App\Services\BackOffice\HuggingFaceApiService;
use App\Services\BackOffice\NovelGeneratorStepService;
use Database\Seeders\AiBrainAiBrainOutputTypeSeeder;
use Database\Seeders\AiBrainOutputTypeSeeder;
use Database\Seeders\AiBrainSeeder;
use Database\Seeders\AiPromptSyncSeeder;
use Database\Seeders\AudienceSeeder;
use Database\Seeders\GenreSeeder;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\NovelGeneratorStepSeeder;
use Database\Seeders\NovelTypeSeeder;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\Concerns\InteractsWithPromptSkeleton;
use Tests\TestCase;

class FoundationGenerationReliabilityTest extends TestCase
{
    use InteractsWithPromptSkeleton;
    use RefreshDatabase;

    private const URL = 'https://router.huggingface.co/v1/chat/completions';

    private const CHAPTER_CONTENT = 'The rain had not stopped for nine days.';

    private User $user;

    private int $requestCount = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_super_admin' => true]);

        $this->seed([
            AudienceSeeder::class,
            GenreSeeder::class,
            NovelTypeSeeder::class,
            LanguageSeeder::class,
            AiBrainSeeder::class,
            AiBrainOutputTypeSeeder::class,
            AiBrainAiBrainOutputTypeSeeder::class,
            AiPromptSyncSeeder::class,
            NovelGeneratorStepSeeder::class,
        ]);

        DB::table('ai_brains')->update(['api_key' => 'test-key']);
    }

    public function test_a_valid_foundation_is_persisted_without_a_retry(): void
    {
        Http::fake([
            self::URL => Http::response($this->completion((string) json_encode($this->foundation())), 200),
        ]);

        $this->createNovel()->assertRedirectContains('/edit');

        Http::assertSentCount(1);

        $novel = Novel::query()->firstOrFail();

        $this->assertSame('sample value', $novel->title);
        $this->assertNotEmpty($novel->world_bible);
        $this->assertNotEmpty($novel->foundation);
        $this->assertSame('completed', $novel->generation_steps[$this->step()->id]['status']);
    }

    public function test_a_missing_world_bible_is_accepted_after_exactly_one_request(): void
    {
        $withoutOptionalFields = $this->foundation();
        unset($withoutOptionalFields['world_bible']);

        Http::fake([
            self::URL => Http::sequence()
                ->push($this->completion((string) json_encode($withoutOptionalFields)), 200)
                ->push($this->completion((string) json_encode($this->foundation())), 200),
        ]);

        $this->createNovel()->assertRedirectContains('/edit');

        Http::assertSentCount(1);

        $contents = $this->sentContents();

        $this->assertCount(1, $contents);
        $this->assertStringNotContainsString('CORRECTION REQUIRED', $contents[0]);

        $novel = Novel::query()->firstOrFail();

        $this->assertSame('sample value', $novel->title);
        $this->assertSame('completed', $novel->generation_steps[$this->step()->id]['status']);
    }

    public function test_a_response_without_a_title_fails_after_exactly_one_request_without_an_automatic_correction(): void
    {
        $unusable = ['sub_title' => 'A subtitle with no title'];

        Http::fake([
            self::URL => Http::sequence()
                ->push($this->completion((string) json_encode($unusable)), 200)
                ->push($this->completion((string) json_encode($this->foundation())), 200),
        ]);

        $this->createNovel();

        Http::assertSentCount(1);

        $contents = $this->sentContents();

        $this->assertCount(1, $contents);
        $this->assertStringNotContainsString('CORRECTION REQUIRED', $contents[0]);

        $this->assertSame(0, Novel::query()->count());
    }

    public function test_an_explicit_second_click_can_generate_after_a_first_failure(): void
    {
        $unusable = ['sub_title' => 'A subtitle with no title'];

        Http::fake([
            self::URL => Http::sequence()
                ->push($this->completion((string) json_encode($unusable)), 200)
                ->push($this->completion((string) json_encode($this->foundation())), 200),
        ]);

        $this->createNovel()->assertRedirect(route('back-office.novels.create'));

        $this->assertSame(0, Novel::query()->count());
        $this->assertSame(1, count(Http::recorded()));

        $this->createNovel()->assertRedirectContains('/edit');

        $this->assertSame(2, count(Http::recorded()));

        $novel = Novel::query()->firstOrFail();

        $this->assertNotEmpty($novel->world_bible);
        $this->assertSame('completed', $novel->generation_steps[$this->step()->id]['status']);
    }

    public function test_a_malformed_response_persists_nothing_and_never_reaches_the_database(): void
    {
        Http::fake([
            self::URL => Http::response($this->completion('{"title": "Truncated'), 200),
        ]);

        $this->createNovel()->assertRedirect(route('back-office.novels.create'));

        $this->assertSame(0, Novel::query()->count());
    }

    public function test_a_timeout_fails_after_exactly_one_request_and_is_reported_without_curl_details(): void
    {
        $this->fakeSuccessfulGeneration();
        $this->createNovel();

        $novel = Novel::query()->firstOrFail();
        $existingFoundation = $novel->foundation;

        $this->reopenFoundation($novel);

        $rawCurl = 'cURL error 28: Operation timed out after 60009 milliseconds with 0 bytes received';
        $attempts = 0;

        Http::fake([
            self::URL => function () use ($rawCurl, &$attempts) {
                $attempts++;

                throw new RuntimeException($rawCurl, 28);
            },
        ]);

        $this->continueGeneration($novel);

        $this->assertSame(1, $attempts);

        $novel->refresh();

        $error = (string) $novel->generation_steps[$this->step()->id]['error'];

        $this->assertSame(NovelHelper::STATUS_FAILED, $novel->status);
        $this->assertSame($existingFoundation, $novel->foundation);
        $this->assertStringNotContainsString('cURL', $error);
        $this->assertStringNotContainsString('60009', $error);
        $this->assertStringNotContainsString('router.huggingface.co', $error);
        $this->assertStringContainsString('did not finish in time', $error);
    }

    public function test_a_timeout_is_logged_with_its_transport_detail(): void
    {
        config([
            'logging.channels.single' => ['driver' => 'single', 'path' => storage_path('logs/laravel.log'), 'level' => 'debug'],
        ]);

        Log::spy();

        Http::fake([
            self::URL => function () {
                throw new RuntimeException('cURL error 28: Operation timed out after 60009 milliseconds', 28);
            },
        ]);

        $this->createNovel();

        Log::shouldHaveReceived('error')
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'AI transport failure.'
                    && str_contains((string) ($context['detail'] ?? ''), 'timed out');
            });

        Log::shouldHaveReceived('error')
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'AI generation request failed.'
                    && ($context['timeout'] ?? 0) > 0;
            });
    }

    public function test_a_server_error_fails_after_exactly_one_request(): void
    {
        Http::fake([
            self::URL => Http::response(['error' => 'overloaded'], 503),
        ]);

        try {
            $this->service()->sendPostRequest(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                self::URL,
                'test-key',
                'test-model',
                'prompt',
            );

            $this->fail('Expected an Exception.');
        } catch (Exception $exception) {
            $this->assertStringContainsString('returned a server error (HTTP 503)', $exception->getMessage());
        }

        Http::assertSentCount(1);
    }

    public function test_a_client_error_fails_after_exactly_one_request(): void
    {
        Http::fake([
            self::URL => Http::response(['error' => 'invalid token'], 401),
        ]);

        try {
            $this->service()->sendPostRequest(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                self::URL,
                'bad-key',
                'test-model',
                'prompt',
            );

            $this->fail('Expected an Exception.');
        } catch (Exception $exception) {
            $this->assertStringContainsString('rejected the request (HTTP 401)', $exception->getMessage());
        }

        Http::assertSentCount(1);
    }

    public function test_a_rate_limit_fails_after_exactly_one_request(): void
    {
        Http::fake([
            self::URL => Http::sequence()
                ->push(['error' => 'slow down'], 429)
                ->push($this->completion((string) json_encode($this->foundation())), 200),
        ]);

        try {
            $this->service()->sendPostRequest(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                self::URL,
                'test-key',
                'test-model',
                'prompt',
            );

            $this->fail('Expected an Exception.');
        } catch (Exception $exception) {
            $this->assertStringContainsString('rate limit was reached', $exception->getMessage());
        }

        Http::assertSentCount(1);
    }

    public function test_an_existing_valid_foundation_survives_a_failed_regeneration(): void
    {
        $this->fakeSuccessfulGeneration();

        $this->createNovel();

        $novel = Novel::query()->firstOrFail();

        $before = [
            'title' => $novel->title,
            'foundation' => $novel->foundation,
            'world_bible' => $novel->world_bible,
            'locations' => $novel->locations,
        ];

        $this->reopenFoundation($novel);

        Http::fake([
            self::URL => Http::response($this->completion('{"title": "partial"'), 200),
        ]);

        $this->continueGeneration($novel);

        Http::assertSentCount(1);

        $novel->refresh();

        $this->assertSame($before['title'], $novel->title);
        $this->assertSame($before['foundation'], $novel->foundation);
        $this->assertSame($before['world_bible'], $novel->world_bible);
        $this->assertSame($before['locations'], $novel->locations);
        $this->assertSame(NovelHelper::STATUS_FAILED, $novel->status);
    }

    public function test_an_existing_valid_plan_chapter_survives_a_failed_regeneration(): void
    {
        $this->fakeSuccessfulGeneration();

        $this->createNovel();

        $novel = Novel::query()->firstOrFail();

        $this->continueGeneration($novel);

        $novel->refresh();

        $before = [
            'title' => $novel->title,
            'chapters' => $novel->chapters,
            'chapter_summaries' => $novel->chapter_summaries,
        ];

        $this->reopenPlanChapter($novel);

        Http::fake([
            self::URL => Http::response($this->completion('{"chapter_plan": '), 200),
        ]);

        $this->continueGeneration($novel);

        Http::assertSentCount(1);

        $novel->refresh();

        $this->assertSame($before['title'], $novel->title);
        $this->assertSame($before['chapters'], $novel->chapters);
        $this->assertSame($before['chapter_summaries'], $novel->chapter_summaries);
        $this->assertSame(NovelHelper::STATUS_FAILED, $novel->status);
        $this->assertSame(
            NovelGeneratorStepService::STATUS_FAILED,
            $novel->generation_steps[$this->planStep()->id]['status'],
        );
    }

    public function test_a_truncated_chapter_content_is_not_persisted(): void
    {
        $this->fakeAi([
            [$this->completion((string) json_encode($this->foundation())), 200],
            [$this->completion((string) json_encode($this->planChapter())), 200],
            [$this->completion('{"chapter_content": "The rain had not', 'length'), 200],
        ]);

        $this->createNovel();

        $novel = Novel::query()->firstOrFail();

        $this->continueGeneration($novel);
        $this->continueGeneration($novel);

        $this->assertSame(3, $this->requestCount);

        $novel->refresh();

        $this->assertSame(NovelHelper::STATUS_FAILED, $novel->status);
        $this->assertNull($novel->novelChapters()->firstOrFail()->content);
        $this->assertSame(
            NovelGeneratorStepService::STATUS_FAILED,
            $novel->generation_steps[$this->chapterStep()->id]['status'],
        );
    }

    public function test_a_failed_stage_does_not_advance_to_the_next_step(): void
    {
        $this->fakeAi([
            [$this->completion((string) json_encode($this->foundation())), 200],
            [$this->completion('{"chapter_plan": '), 200],
        ]);

        $this->createNovel();

        $novel = Novel::query()->firstOrFail();

        $this->continueGeneration($novel);

        $this->assertSame(2, $this->requestCount);

        $progress = $novel->refresh()->generation_steps;

        $this->assertSame(NovelGeneratorStepService::STATUS_FAILED, $progress[$this->planStep()->id]['status']);
        $this->assertSame(NovelGeneratorStepService::STATUS_PENDING, $progress[$this->chapterStep()->id]['status']);
        $this->assertSame(NovelHelper::STATUS_FAILED, $novel->status);
    }

    public function test_the_plan_chapter_request_carries_the_persisted_foundation(): void
    {
        $this->fakeSuccessfulGeneration();

        $this->createNovel();

        $novel = Novel::query()->firstOrFail();

        $this->continueGeneration($novel);

        $this->continueGeneration($novel);

        $contents = $this->sentContents();

        $this->assertCount(3, $contents);
        $this->assertStringContainsString('WORLD BIBLE', $contents[1]);
        $this->assertStringContainsString('CHARACTERS', $contents[1]);
        $this->assertStringContainsString('sample value', $contents[1]);

        $novel->refresh();

        $this->assertSame(NovelHelper::STATUS_COMPLETE, $novel->status);
        $this->assertSame(self::CHAPTER_CONTENT, $novel->novelChapters()->first()->content);
    }

    public function test_opening_an_existing_novel_issues_no_ai_request(): void
    {
        $novel = $this->existingNovelWithFoundation();

        Http::fake();

        $this->actingAs($this->user)
            ->get(route('back-office.novels.edit', $novel->slug))
            ->assertOk();

        foreach (Http::recorded() as $pair) {
            $this->assertStringNotContainsString('router.huggingface.co', $pair[0]->url());
            $this->assertStringNotContainsString('huggingface.co', $pair[0]->url());
        }
    }

    private function service(): HuggingFaceApiService
    {
        return app(HuggingFaceApiService::class);
    }

    private function fakeSuccessfulGeneration(): void
    {
        $this->fakeAi([
            [$this->completion((string) json_encode($this->foundation())), 200],
            [$this->completion((string) json_encode($this->planChapter())), 200],
            [$this->completion((string) json_encode(['chapter_content' => self::CHAPTER_CONTENT])), 200],
        ]);
    }

    private function fakeAi(array $responses): void
    {
        $this->requestCount = 0;

        Http::fake(function () use ($responses) {
            $index = $this->requestCount;
            $this->requestCount++;

            if (! array_key_exists($index, $responses)) {
                return Http::response([
                    'error' => sprintf('Unexpected AI request %d: no fake response remains.', $index + 1),
                ], 599);
            }

            return Http::response($responses[$index][0], $responses[$index][1]);
        });
    }

    private function reopenFoundation(Novel $novel): void
    {
        $this->reopenStep($novel, $this->step());
    }

    private function reopenPlanChapter(Novel $novel): void
    {
        $this->reopenStep($novel, $this->planStep());
    }

    private function reopenChapterContent(Novel $novel): void
    {
        $this->reopenStep($novel, $this->chapterStep());
    }

    private function reopenStep(Novel $novel, NovelGeneratorStep $step): void
    {
        $progress = $novel->generation_steps;
        $progress[$step->id]['status'] = NovelGeneratorStepService::STATUS_PENDING;

        $novel->generation_steps = $progress;
        $novel->status = NovelHelper::STATUS_ONGOING;
        $novel->save();
    }

    private function sentContents(): array
    {
        $contents = [];

        foreach (Http::recorded() as [$request, $response]) {
            $contents[] = $request->data()['messages'][0]['content'];
        }

        return $contents;
    }

    private function existingNovelWithFoundation(): Novel
    {
        $foundation = $this->foundation();

        $novel = new Novel;

        $novel->title = $foundation['title'];
        $novel->sub_title = $foundation['sub_title'];
        $novel->language_id = DB::table('languages')->value('id');
        $novel->novel_type_id = DB::table('novel_types')->value('id');
        $novel->audience_id = DB::table('audiences')->value('id');
        $novel->ai_brain_id = DB::table('ai_brains')->value('id');
        $novel->additional_information = 'A story about a drowned archive.';
        $novel->status = NovelHelper::STATUS_ONGOING;
        $novel->created_by_id = $this->user->id;
        $novel->generation_steps = app(NovelGeneratorStepService::class)->initializeProgress();
        $novel->foundation = $foundation['novel_foundation'];
        $novel->characters = $foundation['characters'];
        $novel->world_bible = $foundation['world_bible'];
        $novel->locations = $foundation['locations'];
        $novel->factions = $foundation['factions'];
        $novel->creatures = $foundation['creatures'];
        $novel->systems = $foundation['systems'];
        $novel->timeline = $foundation['timeline'];

        $novel->save();

        return $novel;
    }

    private function createNovel()
    {
        return $this->actingAs($this->user)->post(route('back-office.novels.create.generate'), $this->payload());
    }

    private function continueGeneration(Novel $novel)
    {
        return $this->actingAs($this->user)->patch(route('back-office.novels.generate', $novel->slug));
    }

    private function step(): NovelGeneratorStep
    {
        return $this->stepNamed(AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION);
    }

    private function planStep(): NovelGeneratorStep
    {
        return $this->stepNamed(AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER);
    }

    private function chapterStep(): NovelGeneratorStep
    {
        return $this->stepNamed(AiPromptGeneratorHelper::AI_PROMPT_NAME_CHAPTER_CONTENT);
    }

    private function stepNamed(string $name): NovelGeneratorStep
    {
        return NovelGeneratorStep::query()
            ->where('name', $name)
            ->firstOrFail();
    }

    private function completion(string $content, string $finishReason = 'stop'): array
    {
        return [
            'choices' => [
                ['message' => ['content' => $content], 'finish_reason' => $finishReason],
            ],
        ];
    }

    private function payload(): array
    {
        return [
            'language_id' => DB::table('languages')->value('id'),
            'genre_ids' => [DB::table('genres')->value('id')],
            'novel_type_id' => DB::table('novel_types')->value('id'),
            'audience_id' => DB::table('audiences')->value('id'),
            'ai_brain_id' => DB::table('ai_brains')->value('id'),
            'additional_information' => 'A story about a drowned archive.',
        ];
    }

    private function foundation(): array
    {
        return self::validPayloadOf(AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION);
    }

    private function planChapter(): array
    {
        $payload = self::validPayloadOf(AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER);

        $payload['chapter_plan'][0]['chapter_number'] = 1;
        $payload['chapter_summaries'][0]['chapter_number'] = 1;

        return $payload;
    }
}
