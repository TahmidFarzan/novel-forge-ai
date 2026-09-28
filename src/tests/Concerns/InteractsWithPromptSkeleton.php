<?php

namespace Tests\Concerns;

use App\Helpers\AiPromptGeneratorHelper;

trait InteractsWithPromptSkeleton
{
    public static function skeletonOf(string $stepName): array
    {
        [$promptMethod, $marker] = self::skeletonSource($stepName);

        $prompt = AiPromptGeneratorHelper::{$promptMethod}();
        $start = strpos($prompt, $marker);

        if ($start === false) {
            return [];
        }

        $rest = substr($prompt, $start);
        $openIndex = strpos($rest, '{');

        if ($openIndex === false) {
            return [];
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

                    return json_last_error() === JSON_ERROR_NONE ? (array) $decoded : [];
                }
            }
        }

        return [];
    }

    public static function validPayloadOf(string $stepName): array
    {
        return self::sampleize(self::skeletonOf($stepName));
    }

    public static function skeletonPaths(array $data, string $prefix = ''): array
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
                    $paths = array_merge($paths, self::skeletonPaths($value[0], $path.'.*'));
                }
            } else {
                $paths = array_merge($paths, self::skeletonPaths($value, $path));
            }
        }

        return $paths;
    }

    public static function sampleize(mixed $value, bool $isList = false): mixed
    {
        if (is_array($value)) {
            if ($value === []) {
                return $isList ? ['sample item'] : [];
            }

            $out = [];

            foreach ($value as $key => $item) {
                $out[$key] = self::sampleize($item, $isList || (is_array($item) && array_is_list($item)));
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

    public static function skeletonSource(string $stepName): array
    {
        return match ($stepName) {
            AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION    => ['foundationPrompt', 'JSON STRUCTURE'],
            AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER     => ['planChapterPrompt', 'JSON STRUCTURE'],
            AiPromptGeneratorHelper::AI_PROMPT_NAME_CHAPTER_CONTENT => ['chapterContentPrompt', 'OUTPUT FORMAT'],
            default => throw new \InvalidArgumentException(sprintf('Unknown novel generation step [%s].', $stepName)),
        };
    }
}
