<?php

namespace App\Services\BackOffice;

use App\Exceptions\AiResponseException;
use App\Helpers\AiStepResponseContractHelper;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;

class AiResponseService
{
    protected const PREVIEW_LENGTH = 200;

    protected const MAX_FENCE_UNWRAP_DEPTH = 2;

    public function contentFromApiResponse(string $stepName, mixed $apiResponse): string
    {
        if (! is_array($apiResponse)) {
            throw AiResponseException::transport(
                $stepName,
                'the provider response was not a JSON object.',
                false,
                ['http_body_type' => get_debug_type($apiResponse)],
            );
        }

        $content = data_get($apiResponse, 'choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw AiResponseException::transport(
                $stepName,
                'the provider response contained no message content.',
                false,
                ['finish_reason' => data_get($apiResponse, 'choices.0.finish_reason')],
            );
        }

        $this->assertNotTruncated($stepName, $apiResponse, $content);

        return $content;
    }

    public function assertNotTruncated(string $stepName, array $apiResponse, string $content): void
    {
        $finishReason = data_get($apiResponse, 'choices.0.finish_reason');

        if ($finishReason !== 'length' && $finishReason !== 'max_tokens') {
            return;
        }

        throw AiResponseException::truncated($stepName, [
            'finish_reason' => $finishReason,
            'content_length' => strlen($content),
            'content_preview' => $this->preview($content),
        ]);
    }

    public function normalize(string $content): string
    {
        $normalized = trim($content);

        for ($depth = 0; $depth < self::MAX_FENCE_UNWRAP_DEPTH; $depth++) {
            if ($this->decodes($normalized)) {
                return $normalized;
            }

            $unwrapped = $this->unwrapCodeFence($normalized);

            if ($unwrapped === null) {
                break;
            }

            $normalized = $unwrapped;
        }

        if ($this->decodes($normalized)) {
            return $normalized;
        }

        $extracted = $this->extractOutermostJsonValue($normalized);

        return $extracted ?? $normalized;
    }

    public function decode(string $content, string $stepName): array
    {
        $normalized = $this->normalize($content);

        $decoded = json_decode($normalized, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw AiResponseException::malformedJson(
                $stepName,
                json_last_error_msg(),
                $this->diagnostics($normalized, $stepName),
            );
        }

        if (! is_array($decoded)) {
            throw AiResponseException::malformedJson(
                $stepName,
                sprintf('expected a JSON object or array, got %s.', get_debug_type($decoded)),
                $this->diagnostics($normalized, $stepName),
            );
        }

        return $this->castIntegerFields($decoded, AiStepResponseContractHelper::integerFields($stepName));
    }

    public function decodeAndValidate(string $content, string $stepName): array
    {
        $decoded = $this->decode($content, $stepName);

        $this->validate($decoded, $stepName);

        return $decoded;
    }

    public function validate(array $decoded, string $stepName): void
    {
        $rules = AiStepResponseContractHelper::rules($stepName);

        if ($rules === []) {
            return;
        }

        $validator = Validator::make($decoded, $rules);

        if ($validator->fails()) {
            throw AiResponseException::structureMismatch($stepName, $this->describeFailures($validator), [
                'failed_keys' => array_keys($validator->errors()->toArray()),
            ]);
        }
    }

    public function describeFailures(ValidatorContract $validator): string
    {
        $messages = $validator->errors()->toArray();

        $summary = [];

        foreach ($messages as $key => $items) {
            $summary[] = sprintf('%s (%s)', $key, implode(' ', $items));
        }

        return implode('; ', array_slice($summary, 0, 8));
    }

    public function diagnostics(string $content, string $stepName): array
    {
        return [
            'step' => $stepName,
            'content_length' => strlen($content),
            'starts_with' => substr($content, 0, 40),
            'ends_with' => substr($content, -40),
            'content_preview' => $this->preview($content),
        ];
    }

    protected function preview(string $content): string
    {
        $collapsed = preg_replace('/\s+/', ' ', trim($content)) ?? trim($content);

        if (mb_strlen($collapsed) <= self::PREVIEW_LENGTH * 2) {
            return $collapsed;
        }

        return mb_substr($collapsed, 0, self::PREVIEW_LENGTH).' … '.mb_substr($collapsed, -self::PREVIEW_LENGTH);
    }

    protected function decodes(string $content): bool
    {
        if ($content === '') {
            return false;
        }

        json_decode($content, true);

        return json_last_error() === JSON_ERROR_NONE;
    }

    protected function unwrapCodeFence(string $content): ?string
    {
        if (! preg_match('/\A```[A-Za-z0-9_+-]*[ \t]*\R/', $content, $open)) {
            return null;
        }

        $body = substr($content, strlen($open[0]));

        if (! preg_match('/\R?```[ \t]*\z/', $body, $close, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $body = substr($body, 0, $close[0][1]);

        return trim($body) === '' ? null : trim($body);
    }

    protected function extractOutermostJsonValue(string $content): ?string
    {
        $length = strlen($content);

        for ($start = 0; $start < $length; $start++) {
            $char = $content[$start];

            if ($char !== '{' && $char !== '[') {
                continue;
            }

            $candidate = $this->readBalanced($content, $start);

            if ($candidate === null) {
                continue;
            }

            if ($this->decodes($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    protected function readBalanced(string $content, int $start): ?string
    {
        $length = strlen($content);
        $depth = 0;
        $inString = false;
        $escaped = false;

        for ($index = $start; $index < $length; $index++) {
            $char = $content[$index];

            if ($inString) {
                if ($escaped) {
                    $escaped = false;

                    continue;
                }

                if ($char === '\\') {
                    $escaped = true;

                    continue;
                }

                if ($char === '"') {
                    $inString = false;
                }

                continue;
            }

            if ($char === '"') {
                $inString = true;

                continue;
            }

            if ($char === '{' || $char === '[') {
                $depth++;

                continue;
            }

            if ($char === '}' || $char === ']') {
                $depth--;

                if ($depth === 0) {
                    return substr($content, $start, $index - $start + 1);
                }

                if ($depth < 0) {
                    return null;
                }
            }
        }

        return null;
    }

    protected function castIntegerFields(array $decoded, array $paths): array
    {
        foreach ($paths as $path) {
            $decoded = $this->castIntegerAtPath($decoded, explode('.', $path));
        }

        return $decoded;
    }

    protected function castIntegerAtPath(array $data, array $segments): array
    {
        $segment = array_shift($segments);

        if ($segment === null) {
            return $data;
        }

        if ($segment === '*') {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = $this->castIntegerAtPath($value, $segments);
                }
            }

            return $data;
        }

        if (! array_key_exists($segment, $data)) {
            return $data;
        }

        if ($segments === []) {
            $data[$segment] = $this->toInteger($data[$segment]);

            return $data;
        }

        if (is_array($data[$segment])) {
            $data[$segment] = $this->castIntegerAtPath($data[$segment], $segments);
        }

        return $data;
    }

    protected function toInteger(mixed $value): mixed
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/\A-?\d+\z/', trim($value)) === 1) {
            return (int) trim($value);
        }

        return $value;
    }
}
