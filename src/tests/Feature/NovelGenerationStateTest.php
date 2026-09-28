<?php

namespace Tests\Feature;

use App\Helpers\AiPromptGeneratorHelper;
use App\Helpers\NovelHelper;
use App\Models\Novel;
use App\Models\NovelGeneratorStep;
use App\Models\User;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\InteractsWithPromptSkeleton;
use Tests\TestCase;

class NovelGenerationStateTest extends TestCase
{
    use InteractsWithPromptSkeleton;
    use RefreshDatabase;

    private const URL = 'https://router.huggingface.co/v1/chat/completions';

    private const CHAPTER_CONTENT = 'The bells of the drowned archive rang once.';

    private User $user;

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

    public function test_the_edit_page_publishes_the_generation_state_used_by_the_frontend(): void
    {
        $this->fakeSuccessfulGeneration();

        $this->createNovel();

        $novel = Novel::query()->firstOrFail();

        $props = $this->editPageProps($novel);

        $this->assertTrue($props['auto']);
        $this->assertSame(NovelHelper::STATUS_ONGOING, $props['generationState']['status']);
        $this->assertSame(1, $props['generationState']['completed_count']);
        $this->assertSame(3, $props['generationState']['total_count']);
        $this->assertFalse($props['generationState']['is_complete']);
        $this->assertSame(
            $this->step(AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION)->id,
            $props['generationState']['latest_completed_step']['id'],
        );
        $this->assertSame(
            $this->step(AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER)->id,
            $props['generationState']['next_step']['id'],
        );
        $this->assertNull($props['generationState']['failed_step']);
    }

    public function test_the_latest_completed_stage_becomes_the_active_stage_after_each_generation(): void
    {
        $this->fakeSuccessfulGeneration();

        $this->createNovel();

        $novel = Novel::query()->firstOrFail();

        $this->continueGeneration($novel);

        $state = $this->editPageProps($novel->refresh())['generationState'];

        $this->assertSame(
            $this->step(AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER)->id,
            $state['latest_completed_step']['id'],
        );
        $this->assertSame(
            $this->step(AiPromptGeneratorHelper::AI_PROMPT_NAME_CHAPTER_CONTENT)->id,
            $state['next_step']['id'],
        );
        $this->assertSame(2, $state['completed_count']);
        $this->assertSame(NovelHelper::STATUS_ONGOING, $state['status']);

        $this->continueGeneration($novel)
            ->assertRedirect(route('back-office.novels.index'));

        $completed = $this->state($novel->refresh());

        $this->assertTrue($completed['is_complete']);
        $this->assertNull($completed['next_step']);
        $this->assertSame(3, $completed['completed_count']);
        $this->assertSame(NovelHelper::STATUS_COMPLETE, $completed['status']);
    }

    public function test_the_latest_completed_stage_survives_a_fresh_page_load(): void
    {
        $this->fakeSuccessfulGeneration();

        $this->createNovel();

        $novel = Novel::query()->firstOrFail();

        $this->continueGeneration($novel);

        $props = $this->editPageProps($novel->refresh());

        $this->assertTrue($props['auto']);
        $this->assertSame(
            $this->step(AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER)->id,
            $props['generationState']['latest_completed_step']['id'],
        );
    }

    public function test_a_successful_generation_keeps_the_auto_flag_in_the_url(): void
    {
        $this->fakeSuccessfulGeneration();

        $this->createNovel();

        $novel = Novel::query()->firstOrFail();

        $this->continueGeneration($novel)
            ->assertRedirect(route('back-office.novels.edit', ['slug' => $novel->slug, 'auto' => 1]));
    }

    public function test_a_failed_stage_is_reported_without_advancing_the_latest_completed_stage(): void
    {
        $this->fakeAi([
            $this->foundation(),
            '{"chapter_plan": ',
        ]);

        $this->createNovel();

        $novel = Novel::query()->firstOrFail();

        $this->continueGeneration($novel)
            ->assertRedirect(route('back-office.novels.edit', ['slug' => $novel->slug]));

        $this->assertSame('error', session('flash_message.status'));

        $props = $this->editPageProps($novel->refresh(), false);
        $state = $props['generationState'];

        $this->assertFalse($props['auto']);
        $this->assertSame(NovelHelper::STATUS_FAILED, $state['status']);
        $this->assertSame(
            $this->step(AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION)->id,
            $state['latest_completed_step']['id'],
        );
        $this->assertSame(
            $this->step(AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER)->id,
            $state['failed_step']['id'],
        );
        $this->assertSame(
            $this->step(AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER)->id,
            $state['next_step']['id'],
        );
        $this->assertSame(1, $state['completed_count']);
        $this->assertNotEmpty($state['steps'][1]['error']);
    }

    public function test_a_failed_stage_can_be_retried_and_generation_resumes(): void
    {
        $this->fakeAi([
            $this->foundation(),
            '{"chapter_plan": ',
            $this->planChapter(),
        ]);

        $this->createNovel();

        $novel = Novel::query()->firstOrFail();

        $this->continueGeneration($novel);

        $this->assertSame(2, $this->aiRequestCount());

        $this->continueGeneration($novel)
            ->assertRedirect(route('back-office.novels.edit', ['slug' => $novel->slug, 'auto' => 1]));

        $state = $this->editPageProps($novel->refresh())['generationState'];

        $this->assertSame(3, $this->aiRequestCount());
        $this->assertNull($state['failed_step']);
        $this->assertSame(NovelHelper::STATUS_ONGOING, $state['status']);
        $this->assertSame(
            $this->step(AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER)->id,
            $state['latest_completed_step']['id'],
        );
        $this->assertSame(
            $this->step(AiPromptGeneratorHelper::AI_PROMPT_NAME_CHAPTER_CONTENT)->id,
            $state['next_step']['id'],
        );
    }

    public function test_a_concurrent_request_does_not_run_the_same_stage_twice(): void
    {
        $this->fakeSuccessfulGeneration();

        $this->createNovel();

        $novel = Novel::query()->firstOrFail();

        Cache::lock(sprintf('novel:%s:generation', $novel->id), 900)->get();

        $this->continueGeneration($novel)
            ->assertRedirect(route('back-office.novels.edit', ['slug' => $novel->slug]));

        $this->assertSame('info', session('flash_message.status'));
        $this->assertSame(1, $this->aiRequestCount());

        $state = $this->editPageProps($novel->refresh(), false)['generationState'];

        $this->assertSame(
            $this->step(AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION)->id,
            $state['latest_completed_step']['id'],
        );
        $this->assertSame(
            $this->step(AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER)->id,
            $state['next_step']['id'],
        );
        $this->assertNull($state['failed_step']);
    }

    public function test_stopping_generation_keeps_progress_and_removes_the_auto_flag(): void
    {
        $this->fakeSuccessfulGeneration();

        $this->createNovel();

        $novel = Novel::query()->firstOrFail();

        $this->actingAs($this->user)
            ->patch(route('back-office.novels.stop', $novel->slug))
            ->assertRedirect(route('back-office.novels.edit', ['slug' => $novel->slug]));

        $this->assertSame(1, $this->aiRequestCount());

        $props = $this->editPageProps($novel->refresh(), false);

        $this->assertFalse($props['auto']);
        $this->assertSame(NovelHelper::STATUS_STOPPED, $props['generationState']['status']);
        $this->assertSame(
            $this->step(AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER)->id,
            $props['generationState']['next_step']['id'],
        );
    }

    public function test_a_stopped_novel_can_be_resumed(): void
    {
        $this->fakeSuccessfulGeneration();

        $this->createNovel();

        $novel = Novel::query()->firstOrFail();

        $this->actingAs($this->user)->patch(route('back-office.novels.stop', $novel->slug));

        $this->continueGeneration($novel)
            ->assertRedirect(route('back-office.novels.edit', ['slug' => $novel->slug, 'auto' => 1]));

        $state = $this->editPageProps($novel->refresh())['generationState'];

        $this->assertSame(
            $this->step(AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER)->id,
            $state['latest_completed_step']['id'],
        );
    }

    private function state(Novel $novel): array
    {
        return app(NovelGeneratorStepService::class)->state($novel);
    }

    private function editPageProps(Novel $novel, bool $auto = true): array
    {
        $parameters = $auto ? ['slug' => $novel->slug, 'auto' => 1] : ['slug' => $novel->slug];

        $response = $this->actingAs($this->user)->get(route('back-office.novels.edit', $parameters));

        $response->assertOk();

        return $response->viewData('page')['props'];
    }

    private function aiRequestCount(): int
    {
        return collect(Http::recorded())
            ->filter(fn (array $pair) => str_contains($pair[0]->url(), parse_url(self::URL, PHP_URL_HOST)))
            ->count();
    }

    private function fakeSuccessfulGeneration(): void
    {
        $this->fakeAi([
            $this->foundation(),
            $this->planChapter(),
            ['chapter_content' => self::CHAPTER_CONTENT],
        ]);
    }

    private function fakeAi(array $payloads): void
    {
        $sequence = Http::sequence();

        foreach ($payloads as $payload) {
            $sequence->push($this->completion(is_string($payload) ? $payload : (string) json_encode($payload)), 200);
        }

        Http::fake([self::URL => $sequence]);
    }

    private function createNovel()
    {
        return $this->actingAs($this->user)->post(route('back-office.novels.create.generate'), $this->payload());
    }

    private function continueGeneration(Novel $novel)
    {
        return $this->actingAs($this->user)->patch(route('back-office.novels.generate', $novel->slug));
    }

    private function step(string $name): NovelGeneratorStep
    {
        return NovelGeneratorStep::query()->where('name', $name)->firstOrFail();
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
