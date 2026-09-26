<?php

namespace App\Support;

class PromptContextEncoder
{
    public static function encode(mixed $value): string
    {
        $encoded = json_encode($value ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (! is_string($encoded)) {
            throw new \RuntimeException(
                'Existing novel data could not be encoded as JSON for the AI prompt: '.json_last_error_msg()
            );
        }

        return $encoded;
    }
}
