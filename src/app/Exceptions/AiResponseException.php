<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class AiResponseException extends Exception
{
    protected bool $retryable;

    protected array $context;

    public function __construct(string $message, bool $retryable = false, array $context = [], int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->retryable = $retryable;
        $this->context = $context;
    }

    public static function malformedJson(string $stepName, string $reason, array $context = []): self
    {
        return new self(
            sprintf('AI response for step "%s" is not valid JSON: %s', $stepName, $reason),
            true,
            $context + ['step' => $stepName, 'reason' => $reason],
        );
    }

    public static function truncated(string $stepName, array $context = []): self
    {
        return new self(
            sprintf('AI response for step "%s" was truncated by the provider before the JSON was complete.', $stepName),
            true,
            $context + ['step' => $stepName, 'reason' => 'truncated'],
        );
    }

    public static function structureMismatch(string $stepName, string $reason, array $context = []): self
    {
        return new self(
            sprintf('AI response for step "%s" does not match the expected structure: %s', $stepName, $reason),
            true,
            $context + ['step' => $stepName, 'reason' => $reason],
        );
    }

    public static function transport(string $stepName, string $reason, bool $retryable, array $context = []): self
    {
        return new self(
            sprintf('AI request for step "%s" failed: %s', $stepName, $reason),
            $retryable,
            $context + ['step' => $stepName, 'reason' => $reason],
        );
    }

    public function isRetryable(): bool
    {
        return $this->retryable;
    }

    public function context(): array
    {
        return $this->context;
    }

    public function withContext(array $context): self
    {
        $clone = new self($this->getMessage(), $this->retryable, $context + $this->context, $this->getCode(), $this->getPrevious());

        $clone->file = $this->file;
        $clone->line = $this->line;

        return $clone;
    }
}
