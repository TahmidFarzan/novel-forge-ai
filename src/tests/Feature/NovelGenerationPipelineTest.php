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
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\InteractsWithPromptSkeleton;
use Tests\TestCase;

class NovelGenerationPipelineTest extends TestCase
{
    use InteractsWithPromptSkeleton;
    use RefreshDatabase;

    private const URL = 'https://router.huggingface.co/v1/chat/completions';

    private const CHAPTER_CONTENT = 'The rain had not stopped for nine days.';

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

    public function test_a_novel_is_generated_with_three_ai_requests(): void
    {
        $this->fakeSuccessfulGeneration();

        $this->createNovel();

        Http::assertSentCount(1);

        $novel = Novel::query()->firstOrFail();

        $this->assertSame('sample value', $novel->title);
        $this->assertSame(NovelHelper::STATUS_ONGOING, $novel->status);

        $this->continueGeneration($novel);
        Http::assertSentCount(2);

        $this->continueGeneration($novel);
        Http::assertSentCount(3);

        $novel->refresh();

        $this->assertSame(NovelHelper::STATUS_COMPLETE, $novel->status);
        $this->assertCount(1, $novel->novelChapters()->get());
        $this->assertSame(self::CHAPTER_CONTENT, $novel->novelChapters()->first()->content);
        $this->assertNotEmpty($novel->foundation);
        $this->assertNotEmpty($novel->characters);
        $this->assertNotEmpty($novel->story_structure);
        $this->assertNotEmpty($novel->page_plan);

        $progress = collect($novel->generation_steps);

        $this->assertCount(3, $progress);
        $this->assertTrue($progress->every(fn (array $state) => $state['status'] === 'completed'));

        $chapter = $novel->novelChapters()->first();

        $this->assertSame('1', $chapter->no);
        $this->assertStringContainsString('chapter_purpose', $chapter->summery);
    }

    public function test_each_request_carries_its_own_prompt(): void
    {
        $this->fakeSuccessfulGeneration();

        $this->createNovel();

        $novel = Novel::query()->firstOrFail();

        $this->continueGeneration($novel);
        $this->continueGeneration($novel);

        $contents = [];

        Http::assertSent(function (Request $request) use (&$contents): bool {
            $contents[] = $request->data()['messages'][0]['content'];

            return true;
        });

        $this->assertCount(3, $contents);
        $this->assertStringContainsString('novel_foundation', $contents[0]);
        $this->assertStringNotContainsString('chapter_summaries', $contents[0]);
        $this->assertStringContainsString('chapter_summaries', $contents[1]);
        $this->assertStringContainsString('chapter_content', $contents[2]);
    }

    public function test_a_failed_request_marks_the_novel_and_the_step_as_failed_and_can_be_resumed(): void
    {
        config([
        ]);

        Http::fake([
            self::URL => Http::sequence()
                ->push($this->completion((string) json_encode($this->foundation())), 200)
                ->push($this->completion('not json at all'), 200)
                ->push($this->completion((string) json_encode($this->planChapter())), 200)
                ->push($this->completion((string) json_encode(['chapter_content' => self::CHAPTER_CONTENT])), 200),
        ]);

        $this->createNovel();

        $novel = Novel::query()->firstOrFail();

        $this->continueGeneration($novel);

        $novel->refresh();

        $this->assertSame(NovelHelper::STATUS_FAILED, $novel->status);

        $planStep = $this->step(AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER);

        $this->assertSame('failed', $novel->generation_steps[$planStep->id]['status']);
        $this->assertNotEmpty($novel->generation_steps[$planStep->id]['error']);
        $this->assertSame('completed', $novel->generation_steps[$this->step(AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION)->id]['status']);

        $this->continueGeneration($novel);
        $this->continueGeneration($novel);

        $novel->refresh();

        $this->assertSame(NovelHelper::STATUS_COMPLETE, $novel->status);
        $this->assertSame('completed', $novel->generation_steps[$planStep->id]['status']);
        $this->assertSame(self::CHAPTER_CONTENT, $novel->novelChapters()->first()->content);
    }

    public function test_a_plan_chapter_without_summaries_still_creates_chapters_with_empty_summaries(): void
    {
        $planChapter = $this->planChapter();
        $planChapter['chapter_summaries'] = [];

        Http::fake([
            self::URL => Http::sequence()
                ->push($this->completion((string) json_encode($this->foundation())), 200)
                ->push($this->completion((string) json_encode($planChapter)), 200),
        ]);

        $this->createNovel();

        $novel = Novel::query()->firstOrFail();

        $this->continueGeneration($novel);

        $novel->refresh();

        $this->assertSame(1, $novel->novelChapters()->count());
        $this->assertSame('', (string) $novel->novelChapters()->firstOrFail()->summery);
        $this->assertSame(2, count(Http::recorded()));
    }

    public function test_a_chapter_is_never_written_when_the_plan_is_unusable(): void
    {
        Http::fake([
            self::URL => Http::sequence()
                ->push($this->completion((string) json_encode($this->foundation())), 200)
                ->push($this->completion('{"story_structure":{"outline":[]}}'), 200),
        ]);

        $this->createNovel();

        $novel = Novel::query()->firstOrFail();

        $this->continueGeneration($novel);

        $novel->refresh();

        $this->assertSame(NovelHelper::STATUS_FAILED, $novel->status);
        $this->assertSame(0, $novel->novelChapters()->count());
        $this->assertSame(2, count(Http::recorded()));
    }

    public function test_a_foundation_failure_does_not_create_a_novel(): void
    {
        config([
        ]);

        Http::fake([
            self::URL => Http::response($this->completion('not json at all'), 200),
        ]);

        $this->createNovel()
            ->assertRedirect(route('back-office.novels.create'));

        $this->assertSame(0, Novel::query()->count());
    }

    public function test_generation_is_rejected_without_a_configured_ai_brain(): void
    {
        Http::fake();

        $novel = $this->novelWithoutAiBrain();

        $this->continueGeneration($novel);

        $this->assertSame(NovelHelper::STATUS_DRAFT, $novel->fresh()->status);

        Http::assertNothingSent();
    }

    public function test_a_completed_novel_is_never_sent_to_the_provider_again(): void
    {
        $this->fakeSuccessfulGeneration();

        $this->createNovel();

        $novel = Novel::query()->firstOrFail();

        $this->continueGeneration($novel);
        $this->continueGeneration($novel);

        $novel->refresh();

        $this->assertSame(NovelHelper::STATUS_COMPLETE, $novel->status);
        $this->assertSame(self::CHAPTER_CONTENT, $novel->novelChapters()->first()->content);

        $this->continueGeneration($novel);

        Http::assertSentCount(3);

        $this->assertSame(NovelHelper::STATUS_COMPLETE, $novel->fresh()->status);
    }

    private function fakeSuccessfulGeneration(): void
    {
        Http::fake([
            self::URL => Http::sequence()
                ->push($this->completion((string) json_encode($this->foundation())), 200)
                ->push($this->completion((string) json_encode($this->planChapter())), 200)
                ->push($this->completion((string) json_encode(['chapter_content' => self::CHAPTER_CONTENT])), 200),
        ]);
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

    private function novelWithoutAiBrain(): Novel
    {
        $novel = new Novel;

        $novel->title = 'Manual draft';
        $novel->sub_title = 'Created by hand.';
        $novel->language_id = DB::table('languages')->value('id');
        $novel->novel_type_id = DB::table('novel_types')->value('id');
        $novel->audience_id = DB::table('audiences')->value('id');
        $novel->status = NovelHelper::STATUS_DRAFT;
        $novel->created_by_id = $this->user->id;
        $novel->generation_steps = app(NovelGeneratorStepService::class)->initializeProgress();

        $novel->save();

        return $novel;
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
