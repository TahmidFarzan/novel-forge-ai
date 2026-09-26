<?php

namespace Tests\Unit;

use App\Exceptions\AiResponseException;
use App\Helpers\AiPromptGeneratorHelper;
use App\Services\BackOffice\HuggingFaceApiService;
use Tests\TestCase;

class HuggingFaceApiServiceResponseFormatTest extends TestCase
{
    private function completion(string $content, string $finishReason = 'stop'): array
    {
        return [
            'choices' => [
                ['message' => ['content' => $content], 'finish_reason' => $finishReason],
            ],
        ];
    }

    private function service(): HuggingFaceApiService
    {
        return app(HuggingFaceApiService::class);
    }

    public function test_step_sixteen_returns_bare_prose_untouched(): void
    {
        $prose = "The rain had not stopped for nine days.\n\nAda counted the cracks in the ceiling.";

        $result = $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP16,
            $this->completion($prose),
        );

        $this->assertSame(['chapter_content' => $prose], $result);
    }

    public function test_step_sixteen_accepts_the_documented_json_fallback(): void
    {
        $result = $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP16,
            $this->completion('{"chapter_content":"The rain had not stopped for nine days."}'),
        );

        $this->assertSame('The rain had not stopped for nine days.', $result['chapter_content']);
    }

    public function test_step_sixteen_rejects_an_empty_json_fallback(): void
    {
        $this->expectException(AiResponseException::class);

        $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP16,
            $this->completion('{"chapter_content":"   "}'),
        );
    }

    public function test_step_sixteen_rejects_an_empty_response(): void
    {
        $this->expectException(AiResponseException::class);

        $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP16,
            $this->completion('   '),
        );
    }

    public function test_step_sixteen_rejects_a_truncated_response(): void
    {
        try {
            $this->service()->aiResponseFormats(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP16,
                $this->completion('The rain had not stopped for nine days. Ada coun', 'length'),
            );

            $this->fail('Expected an AiResponseException for a truncated chapter.');
        } catch (AiResponseException $exception) {
            $this->assertSame('truncated', $exception->context()['reason']);
            $this->assertTrue($exception->isRetryable());
        }
    }

    public function test_step_one_maps_the_nested_foundation(): void
    {
        $result = $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP1,
            $this->completion((string) json_encode([
                'novel_title' => 'The Mnemosyne Fracture',
                'novel_subtitle' => 'A memory is not a witness',
                'novel_foundation' => $this->foundationPayload(),
            ])),
        );

        $this->assertSame('The Mnemosyne Fracture', $result['title']);
        $this->assertSame('A memory is not a witness', $result['subtitle']);
        $this->assertSame('An archivist discovers her own memories are forged.', $result['foundation']['premise']);
        $this->assertSame(['Ada'], $result['foundation']['important_character_roles']);
    }

    public function test_step_one_rejects_a_foundation_missing_a_field(): void
    {
        $foundation = $this->foundationPayload();

        unset($foundation['premise']);

        $this->expectException(AiResponseException::class);

        $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP1,
            $this->completion((string) json_encode([
                'novel_title' => 'The Mnemosyne Fracture',
                'novel_subtitle' => 'A memory is not a witness',
                'novel_foundation' => $foundation,
            ])),
        );
    }

    private function foundationPayload(): array
    {
        return [
            'premise' => 'An archivist discovers her own memories are forged.',
            'story_concept' => 'Memory as evidence.',
            'narrative_hook' => 'A seal that should not break.',
            'central_question' => 'Who is forging the record?',
            'central_theme' => 'Truth versus memory.',
            'emotional_direction' => 'Doubt hardening into resolve.',
            'setting' => 'A drowned archive city.',
            'main_character_direction' => 'An archivist who trusts records over people.',
            'important_character_roles' => ['Ada'],
            'central_motivation' => 'Prove her own memory.',
            'central_goal' => 'Open the sealed ledger.',
            'character_journey_direction' => 'From compliance to defiance.',
            'central_conflict' => 'Ada against the Archivist.',
            'opposing_force' => 'An institution that edits the past.',
            'stakes' => 'The archive itself.',
            'consequences' => 'Her identity is rewritten.',
            'opening_situation' => 'Ada catalogues a wet ledger.',
            'inciting_event' => 'The seal breaks itself.',
            'initial_goal' => 'Log the damage.',
            'major_complications' => ['A forged page.'],
            'discoveries' => ['Her handwriting is wrong.'],
            'turning_points' => ['The ledger names her.'],
            'escalation' => 'The archive closes ranks.',
            'climax_direction' => 'Ada reads the ledger aloud.',
            'resolution_direction' => 'The record is corrected in public.',
            'themes' => ['Memory', 'Evidence'],
        ];
    }

    public function test_step_two_keeps_relationships_next_to_characters(): void
    {
        $result = $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2,
            $this->completion((string) json_encode([
                'characters' => [
                    [
                        'name' => 'Ada', 'role' => 'protagonist', 'character_type' => 'human',
                        'personality' => 'curious', 'appearance_direction' => 'lean',
                        'background_direction' => 'engineer', 'motivation' => 'truth', 'goal' => 'escape',
                        'strengths' => ['focus'], 'weaknesses' => ['stubborn'],
                        'internal_conflict' => 'fear', 'external_conflict' => 'empire',
                        'relationship_to_main_character' => 'self', 'character_arc_direction' => 'grows',
                    ],
                ],
                'relationships' => [
                    ['characters' => 'Ada and Bo', 'relationship' => 'siblings', 'story_purpose' => 'tension'],
                ],
            ])),
        );

        $this->assertSame(
            ['characters', 'relationships'],
            array_keys($result['characters']),
        );

        $this->assertSame('Ada', $result['characters']['characters'][0]['name']);
        $this->assertSame('siblings', $result['characters']['relationships'][0]['relationship']);
    }

    public function test_step_fifteen_encodes_the_summary_for_storage(): void
    {
        $result = $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP15,
            $this->completion((string) json_encode([
                'chapter_number' => 3,
                'chapter_title' => 'The Forged Memory',
                'chapter_summary' => [
                    'chapter_purpose' => 'Ada discovers the archive lies.',
                    'progression' => 'Ada moves from doubt to action.',
                    'emotional_and_narrative_movement' => 'Defence becomes resolve.',
                    'chapter_ending_and_setup' => 'Ada opens the sealed ledger.',
                    'chapter_goals' => ['Verify the seal.'],
                    'characters_involved' => ['Ada'],
                    'important_events' => ['The seal is broken.'],
                    'relevant_conflicts' => ['Ada against the Archivist.'],
                    'important_revelations' => ['Ada is the forger.'],
                    'scene_progression' => ['Ada alone in the archive.'],
                    'continuity_requirements' => ['The ledger stays sealed.'],
                ],
            ])),
        );

        $this->assertIsString($result['chapter_summary']);

        $decoded = json_decode($result['chapter_summary'], true);

        $this->assertSame('Ada discovers the archive lies.', $decoded['chapter_purpose']);
        $this->assertSame(['Ada'], $decoded['characters_involved']);
    }

    public function test_step_fifteen_rejects_an_empty_summary(): void
    {
        $this->expectException(AiResponseException::class);

        $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP15,
            $this->completion('{"chapter_number":3,"chapter_title":"T","chapter_summary":[]}'),
        );
    }

    public function test_it_rejects_an_unknown_step_name(): void
    {
        $this->expectException(\Exception::class);

        $this->service()->aiResponseFormats('Mystery Step', $this->completion('{"a":1}'));
    }
}
