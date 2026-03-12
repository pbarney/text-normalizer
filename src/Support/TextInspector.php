<?php

declare(strict_types=1);

namespace TextNormalizer\Support;

final class TextInspector
{
    public static function looksDamagedTitleCase(string $text): bool
    {
        preg_match_all('/\b\p{L}[\p{L}\']*\b/u', $text, $matches);
        $words = $matches[0] ?? [];

        if ($words === []) {
            return false;
        }

        $capitalized = 0;
        $eligible = 0;

        foreach ($words as $word) {
            if (mb_strlen($word) < 3) {
                continue;
            }

            $eligible++;
            $first = mb_substr($word, 0, 1);
            $rest = mb_substr($word, 1);

            if (mb_strtoupper($first) === $first && mb_strtolower($rest) === $rest) {
                $capitalized++;
            }
        }

        if ($eligible === 0) {
            return false;
        }

        return ($capitalized / $eligible) >= 0.55;
    }

    public static function containsShortUppercaseTokens(string $text): bool
    {
        return preg_match('/\b(?:US|OR|IN|OF|TO|BY|AT|ON|LLC|INC|LTD|HVAC|EPA|OSHA)\b/', $text) === 1;
    }

    public static function containsParentheticalAcronymPattern(string $text): bool
    {
        return preg_match('/\(\s*[A-Z]{2,8}\s*\)/', $text) === 1;
    }

    public static function containsBusinessJoiners(string $text): bool
    {
        return preg_match('/[&\/\-]/', $text) === 1;
    }

    public static function isMultiSentence(string $text): bool
    {
        return preg_match_all('/[.!?]+/', $text) >= 2;
    }
}