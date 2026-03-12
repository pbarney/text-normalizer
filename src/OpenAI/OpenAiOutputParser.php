<?php

declare(strict_types=1);

namespace TextNormalizer\OpenAI;

use JsonException;
use TextNormalizer\Exception\ApiException;

final class OpenAiOutputParser
{
    /**
     * @param array<string, mixed> $data
     */
    public function extractNormalizedText(array $data): string
    {
        $raw = trim($this->extractRawText($data));

        if ($raw === '') {
            throw new ApiException('OpenAI API response did not contain any text output.');
        }

        $decoded = $this->tryDecodeJson($raw);

        if (! is_array($decoded) || ! isset($decoded['normalized_text']) || ! is_string($decoded['normalized_text'])) {
            throw new ApiException('Structured output did not contain normalized_text.');
        }

            $normalized = trim($decoded['normalized_text']);

            if ($normalized === '') {
                throw new ApiException('Structured output contained an empty normalized_text value.');
            }

            return $normalized;
        }

    /**
     * @param array<string, mixed> $data
     */
    private function extractRawText(array $data): string
    {
        if (isset($data['output_text']) && is_string($data['output_text'])) {
            return $data['output_text'];
        }

        if (! isset($data['output']) || ! is_array($data['output'])) {
            throw new ApiException('OpenAI API response did not include output_text or output blocks.');
        }

        foreach ($data['output'] as $outputItem) {
            if (! is_array($outputItem)) {
                continue;
            }

            $content = $outputItem['content'] ?? null;
            if (! is_array($content)) {
                continue;
            }

            foreach ($content as $contentItem) {
                if (! is_array($contentItem)) {
                    continue;
                }

                $type = $contentItem['type'] ?? null;

                if ($type === 'refusal') {
                    $message = $contentItem['refusal'] ?? 'Model refused the request.';
                    $message = is_string($message) && trim($message) !== ''
                        ? trim($message)
                        : 'Model refused the request.';

                    throw new ApiException('OpenAI model refusal: ' . $message);
                }

                $text = $contentItem['text'] ?? null;
                if (is_string($text) && trim($text) !== '') {
                    return $text;
                }
            }
        }

        throw new ApiException('OpenAI API output blocks did not contain extractable text.');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function tryDecodeJson(string $raw): ?array
    {
        $firstChar = $raw[0] ?? '';
        if ($firstChar !== '{' && $firstChar !== '[') {
            return null;
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }
}