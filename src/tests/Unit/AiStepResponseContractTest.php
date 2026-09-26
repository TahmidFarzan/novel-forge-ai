<?php

namespace Tests\Unit;

use App\Helpers\AiPromptGeneratorHelper;
use App\Helpers\AiStepResponseContractHelper;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AiStepResponseContractTest extends TestCase
{
    public static function stepProvider(): array
    {
        $cases = [];

        for ($step = 1; $step <= 15; $step++) {
            $constant = 'AI_PROMPT_NAME_STEP'.$step;

            $cases['step'.$step] = [$step, constant(AiPromptGeneratorHelper::class.'::'.$constant)];
        }

        return $cases;
    }

    #[DataProvider('stepProvider')]
    public function test_the_step_skeleton_is_valid_json(int $step): void
    {
        $prompt = AiPromptGeneratorHelper::{'step'.$step.'Prompt'}();

        $this->assertNotNull(
            $this->skeleton($prompt),
            sprintf('Step %d prompt does not contain a parsable JSON skeleton.', $step),
        );
    }

    #[DataProvider('stepProvider')]
    public function test_every_skeleton_field_is_enforced_by_the_contract(int $step, string $stepName): void
    {
        $skeleton = $this->skeleton(AiPromptGeneratorHelper::{'step'.$step.'Prompt'}());

        $rules = AiStepResponseContractHelper::rules($stepName);
        $unforced = array_values(array_filter(
            $this->skeletonPaths($skeleton),
            fn (string $path) => ! in_array($path, array_keys($rules), true),
        ));

        $this->assertSame([], $unforced, sprintf('Step %d skeleton fields have no validation rule.', $step));
    }

    #[DataProvider('stepProvider')]
    public function test_every_contract_rule_exists_in_the_step_skeleton(int $step, string $stepName): void
    {
        $skeleton = $this->skeleton(AiPromptGeneratorHelper::{'step'.$step.'Prompt'}());
        $paths = $this->skeletonPaths($skeleton);

        $ghosts = array_values(array_filter(
            array_keys(AiStepResponseContractHelper::rules($stepName)),
            fn (string $key) => ! in_array($key, $paths, true),
        ));

        $this->assertSame([], $ghosts, sprintf('Step %d contract validates fields the prompt never requests.', $step));
    }

    #[DataProvider('stepProvider')]
    public function test_the_contract_accepts_a_payload_that_follows_its_own_prompt(int $step, string $stepName): void
    {
        $skeleton = $this->skeleton(AiPromptGeneratorHelper::{'step'.$step.'Prompt'}());
        $validator = Validator::make($this->sampleize($skeleton), AiStepResponseContractHelper::rules($stepName));

        $this->assertFalse(
            $validator->fails(),
            sprintf('Step %d rejects its own documented output shape: %s', $step, json_encode($validator->errors()->toArray())),
        );
    }

    #[DataProvider('stepProvider')]
    public function test_the_prompt_declares_a_mandatory_output_contract(int $step, string $stepName): void
    {
        if ($step === 16) {
            $this->assertStringNotContainsString('OUTPUT CONTRACT', AiPromptGeneratorHelper::step16Prompt());

            return;
        }

        $prompt = AiPromptGeneratorHelper::{'step'.$step.'Prompt'}();

        $this->assertStringContainsString('OUTPUT CONTRACT', $prompt);
        $this->assertStringContainsString('Do not', $prompt);
        $this->assertStringContainsString('Escape every double quote', $prompt);
        $this->assertStringNotContainsString('Return only JSON.', $prompt);
    }

    public function test_no_prompt_leaves_an_unresolved_placeholder(): void
    {
        $formatterKeys = [
            1 => ['language', 'additional_information', 'genre_prompt_instruction', 'audience_instruction', 'novel_type_instruction'],
            2 => ['foundation'],
            3 => ['foundation', 'characters'],
            4 => ['world_bible', 'characters'],
            5 => ['world_bible', 'locations'],
            6 => ['world_bible', 'locations', 'factions'],
            7 => ['world_bible', 'creatures', 'factions'],
            8 => ['world_bible', 'factions', 'foundation'],
            9 => ['foundation', 'characters', 'world_bible', 'timeline'],
            10 => ['story_structure', 'characters', 'world_bible'],
            11 => ['story_structure', 'twists_and_foreshadowing', 'locations'],
            12 => ['characters', 'scene_plans'],
            13 => ['scene_plans', 'story_structure'],
            14 => ['chapter_plan', 'scene_plans'],
            15 => ['foundation', 'characters', 'story_structure', 'twists_and_foreshadowing', 'chapter_plan_entry', 'scene_plans'],
            16 => ['language', 'foundation', 'characters', 'world_bible', 'story_structure', 'twists_and_foreshadowing', 'chapter_summary', 'chapter_plan_entry', 'scene_plans', 'dialogue_plans'],
        ];

        foreach ($formatterKeys as $step => $keys) {
            $inputs = array_combine($keys, array_map(fn (string $key) => '<'.$key.'>', $keys));
            $full = AiPromptGeneratorHelper::generateFullPrompt(
                AiPromptGeneratorHelper::{'step'.$step.'Prompt'}(),
                $inputs,
            );

            $this->assertSame(
                [],
                AiPromptGeneratorHelper::unresolvedPlaceholders($full),
                sprintf('Step %d leaves a placeholder unresolved.', $step),
            );
        }
    }

    public function test_step_one_uses_the_genre_placeholder_that_the_formatter_supplies(): void
    {
        $this->assertStringContainsString('{{genre_prompt_instruction}}', AiPromptGeneratorHelper::step1Prompt());
        $this->assertStringNotContainsString('{{genre_instructions}}', AiPromptGeneratorHelper::step1Prompt());
    }

    private function skeleton(string $prompt): ?array
    {
        $start = strpos($prompt, 'OUTPUT FORMAT');

        if ($start === false) {
            return null;
        }

        $rest = substr($prompt, $start);
        $openIndex = strpos($rest, '{');

        if ($openIndex === false) {
            return null;
        }

        $length = strlen($rest);
        $depth = 0;
        $inString = false;
        $escaped = false;

        for ($index = $openIndex; $index < $length; $index++) {
            $char = $rest[$index];

            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($char === '\\') {
                    $escaped = true;
                } elseif ($char === '"') {
                    $inString = false;
                }

                continue;
            }

            if ($char === '"') {
                $inString = true;
            } elseif ($char === '{' || $char === '[') {
                $depth++;
            } elseif ($char === '}' || $char === ']') {
                $depth--;

                if ($depth === 0) {
                    $decoded = json_decode(substr($rest, $openIndex, $index - $openIndex + 1), true);

                    return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
                }
            }
        }

        return null;
    }

    private function sampleize(mixed $value, bool $isList = false): mixed
    {
        if (is_array($value)) {
            if ($value === []) {
                return $isList ? ['sample item'] : [];
            }

            $out = [];

            foreach ($value as $key => $item) {
                $out[$key] = $this->sampleize($item, $isList || (is_array($item) && array_is_list($item)));
            }

            return $out;
        }

        if (is_int($value)) {
            return 7;
        }

        if (is_float($value)) {
            return 1.5;
        }

        if (is_bool($value)) {
            return true;
        }

        return 'sample value';
    }

    private function skeletonPaths(array $data, string $prefix = ''): array
    {
        $paths = [];

        foreach ($data as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            $paths[] = $path;

            if (! is_array($value) || $value === []) {
                continue;
            }

            if (array_is_list($value)) {
                if (is_array($value[0])) {
                    $paths = array_merge($paths, $this->skeletonPaths($value[0], $path.'.*'));
                }
            } else {
                $paths = array_merge($paths, $this->skeletonPaths($value, $path));
            }
        }

        return $paths;
    }
}
