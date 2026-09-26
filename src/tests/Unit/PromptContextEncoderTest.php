<?php

namespace Tests\Unit;

use App\Support\PromptContextEncoder;
use RuntimeException;
use Tests\TestCase;

class PromptContextEncoderTest extends TestCase
{
    public function test_it_encodes_an_empty_value_as_an_empty_object(): void
    {
        $this->assertSame('[]', trim(PromptContextEncoder::encode(null)));
        $this->assertSame('[]', trim(PromptContextEncoder::encode([])));
    }

    public function test_it_encodes_nested_structures_readably(): void
    {
        $encoded = PromptContextEncoder::encode([
            'characters' => [
                ['name' => 'Ada'],
            ],
        ]);

        $this->assertStringContainsString('"characters"', $encoded);
        $this->assertStringContainsString('"Ada"', $encoded);
        $this->assertJson($encoded);
    }

    public function test_it_keeps_unicode_readable(): void
    {
        $encoded = PromptContextEncoder::encode(['setting' => 'München — 中文']);

        $this->assertStringContainsString('München — 中文', $encoded);
    }

    public function test_it_fails_loudly_on_unencodable_data(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Existing novel data could not be encoded as JSON');

        PromptContextEncoder::encode(['broken' => "\xB1\x31"]);
    }
}
