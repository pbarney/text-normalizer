<?php

declare(strict_types=1);

namespace TextNormalizer\OpenAI;

use TextNormalizer\Contract\AiNormalizerInterface;
use TextNormalizer\Result\AiNormalizationResponse;

final class OpenAiAiNormalizer implements AiNormalizerInterface
{
    public function __construct(
        private readonly OpenAiResponsesClient $client,
        private readonly string $model,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function normalize(string $text, array $context = []): AiNormalizationResponse
    {
        $prompt = $this->buildPrompt($text, $context);
        $normalized = $this->client->normalizeText(
            model: $this->model,
            prompt: $prompt,
        );

        return new AiNormalizationResponse(
            normalizedText: $normalized,
            model: $this->model,
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    private function buildPrompt(string $text, array $context): string
    {
        $contextLines = [];

        if (! empty($context['acronyms']) && is_array($context['acronyms'])) {
            $contextLines[] = 'Known acronyms: ' . implode(', ', array_filter($context['acronyms'], 'is_string'));
        }

        if (! empty($context['protected_phrases']) && is_array($context['protected_phrases'])) {
            $contextLines[] = 'Known protected phrases: ' . implode(', ', array_filter($context['protected_phrases'], 'is_string'));
        }

        $contextBlock = $contextLines === []
            ? 'No additional context provided.'
            : implode("\n", $contextLines);

        return <<<PROMPT
Normalize the capitalization and punctuation of the following business-oriented text for user-facing display.

Rules:
- Preserve meaning.
- Do not summarize.
- Do not add facts.
- Preserve acronyms and proper names when reasonably inferable.
- Preserve any phrases and acronyms listed in Context exactly.
- Return only the normalized text.

Context:
{$contextBlock}

Text:
{$text}
PROMPT;
    }
}