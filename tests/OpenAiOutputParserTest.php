<?php

declare(strict_types=1);

namespace TextNormalizer\Tests;

use PHPUnit\Framework\TestCase;
use TextNormalizer\Exception\ApiException;
use TextNormalizer\OpenAI\OpenAiOutputParser;

final class OpenAiOutputParserTest extends TestCase
{
    public function testExtractsNormalizedTextFromStructuredOutputText(): void
    {
        $parser = new OpenAiOutputParser();

        $result = $parser->extractNormalizedText([
            'output_text' => '{"normalized_text":"Hello world."}',
        ]);

        self::assertSame('Hello world.', $result);
    }

    public function testExtractsNormalizedTextFromNestedStructuredOutput(): void
    {
        $parser = new OpenAiOutputParser();

        $result = $parser->extractNormalizedText([
            'output' => [
                [
                    'content' => [
                        [
                            'type' => 'output_text',
                            'text' => '{"normalized_text":"Hello world."}',
                        ],
                    ],
                ],
            ],
        ]);

        self::assertSame('Hello world.', $result);
    }

    public function testThrowsOnRefusal(): void
    {
        $parser = new OpenAiOutputParser();

        $this->expectException(ApiException::class);

        $parser->extractNormalizedText([
            'output' => [
                [
                    'content' => [
                        [
                            'type' => 'refusal',
                            'refusal' => 'Cannot comply.',
                        ],
                    ],
                ],
            ],
        ]);
    }
}