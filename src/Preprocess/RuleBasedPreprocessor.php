<?php

declare(strict_types=1);

namespace TextNormalizer\Preprocess;

final class RuleBasedPreprocessor
{
    /**
     * @param array<string, mixed> $context
     */
    public function normalize(string $text, array $context = []): string
    {
        $text = trim($text);

        if ($text === '') {
            return '';
        }

        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        $text = mb_strtolower($text);
        $text = preg_replace('/\bi\b/u', 'I', $text) ?? $text;
        $text = preg_replace_callback(
            '/(^|[.!?]\s+)(\p{L})/u',
            static fn (array $matches): string => $matches[1] . mb_strtoupper($matches[2]),
            $text,
        ) ?? $text;

        $text = str_replace(['( ', ' )'], ['(', ')'], $text);
        $text = preg_replace('/\s+([,!.?;:])/u', '$1', $text) ?? $text;

        $acronyms = $context['acronyms'] ?? [];
        if (is_array($acronyms)) {
            foreach ($acronyms as $acronym) {
                if (! is_string($acronym) || $acronym === '') {
                    continue;
                }

                $pattern = '/\b' . preg_quote(mb_strtolower($acronym), '/') . '\b/u';
                $text = preg_replace($pattern, $acronym, $text) ?? $text;
            }
        }

        $protectedPhrases = $context['protected_phrases'] ?? [];
        if (is_array($protectedPhrases)) {
            foreach ($protectedPhrases as $phrase) {
                if (! is_string($phrase) || $phrase === '') {
                    continue;
                }

                $lowerPhrase = mb_strtolower($phrase);
                $pattern = preg_match('/\s/u', $phrase) === 1
                    ? '/' . preg_quote($lowerPhrase, '/') . '/u'
                    : '/\b' . preg_quote($lowerPhrase, '/') . '\b/u';

                $text = preg_replace($pattern, $phrase, $text) ?? $text;
            }
        }

        return $text;
    }
}