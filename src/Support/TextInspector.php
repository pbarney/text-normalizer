<?php

declare(strict_types=1);

namespace TextNormalizer\Support;

final class TextInspector
{
    /**
     * Detects whether the text appears to be mechanically title-cased.
     *
     * This is intended to catch text where an unusually large share of longer
     * words follow a Title Case pattern, which is a common sign that source text
     * was normalized badly before import.
     */
    public static function looksDamagedTitleCase(string $text): bool
    {
        // extract word-like tokens
        preg_match_all('/\b\p{L}[\p{L}\']*\b/u', $text, $matches);
        $words = $matches[0] ?? [];

        if ($words === []) {
            return false;
        }

        $capitalized = 0;
        $eligible = 0;

        // ignore very short words (shorter than 3 characters)
        foreach ($words as $word) {
            if (mb_strlen($word) < 3) {
                continue;
            }

            $eligible++;
            $first = mb_substr($word, 0, 1);
            $rest = mb_substr($word, 1);

            // for the remaining words, count how many have the first letter capitalized
            // and all the remaining letters are lowercase (for example, Whether, Building, Letter)
            if (mb_strtoupper($first) === $first && mb_strtolower($rest) === $rest) {
                $capitalized++;
            }
        }

        if ($eligible === 0) {
            return false;
        }

        // if > 55% of those eligible words match that pattern, the text looks damaged to this method
        return ($capitalized / $eligible) >= 0.55;
    }

    /**
     * Detects likely OCR-style digit/letter substitutions inside words.
     *
     * Examples include values such as "qu1nine", "3ist", or "N0", where digits
     * appear inside otherwise word-like tokens in a way that suggests OCR damage
     * rather than legitimate numeric content.
     */
    public static function containsDigitLetterConfusion(string $text): bool
    {
        return preg_match(
            '/\b(?=[\p{L}\p{N}]*\p{L})(?=[\p{L}\p{N}]*\p{N})[\p{L}\p{N}]+\b/u',
            $text
        ) === 1;
    }

    /**
     * Detects broken possessives or contractions caused by spacing artifacts.
     *
     * This is intended to catch text such as "Company S", "Clerk S", or "Wasn T",
     * where apostrophes or letter groups appear to have been split apart during
     * OCR or transcription.
     */
    public static function containsBrokenPossessiveOrContraction(string $text): bool
    {
        return preg_match(
            '/\b\p{L}{2,}\s+(?:s|t|ll|re|ve|m)\b/iu',
            $text
        ) === 1;
    }

    /**
     * Detects words that appear to have been split unnaturally into separate parts.
     *
     * Examples include forms such as "arriv d", "sudden ly", or "them selves",
     * which often indicate OCR or transcription artifacts rather than intentional
     * spelling.
     */
    public static function containsSplitWordArtifact(string $text): bool
    {
        return preg_match(
            '/\b\p{L}{3,}\s+(?:d|ly)\b/iu',
            $text
        ) === 1
        || preg_match(
            '/\b(?:my|your|his|her|our|their|them|it)\s+sel(?:f|ves)\b/iu',
            $text
        ) === 1;
    }

    /**
     * Detects suspicious numeric fragments that suggest OCR uncertainty.
     *
     * This is intended to catch values such as "4?0" or "7?", where part of a
     * number appears unreadable or corrupted, without attempting to infer the
     * original intended value.
     */
    public static function containsUncertainNumericArtifact(string $text): bool
    {
        return preg_match(
            '/\b\d+\?\d*\b|\b\d*\?\d+\b/u',
            $text
        ) === 1;
    }

    /**
     * Detects short all-caps tokens that are often ambiguous in damaged text.
     *
     * Examples include "US", "OR", or "IN". These do not necessarily indicate
     * damage on their own, but they are useful as ambiguity factors when deciding
     * whether AI-assisted normalization may be warranted.
     */
    public static function containsShortUppercaseTokens(string $text): bool
    {
        return preg_match('/\b(?:US|OR|IN|OF|TO|BY|AT|ON|LLC|INC|LTD|HVAC|EPA|OSHA)\b/', $text) === 1;
    }

    /**
     * Detects acronym-like patterns enclosed in parentheses.
     *
     * Examples include "(MADD)" or "( OSHA )". These patterns are useful as
     * ambiguity factors because they often require careful preservation of casing
     * and spacing during normalization.
     */
    public static function containsParentheticalAcronymPattern(string $text): bool
    {
        return preg_match('/\(\s*[A-Z]{2,8}\s*\)/', $text) === 1;
    }

    /**
     * Detects joiner characters commonly found in names and compound phrases.
     *
     * This includes characters such as "&", "/", or "-", which often appear in
     * organization names, branded terms, or other phrases that may require more
     * careful normalization.
     */
    public static function containsBusinessJoiners(string $text): bool
    {
        return preg_match('/[&\/\-]/', $text) === 1;
    }

    /**
     * Detects whether the text contains multiple sentences.
     *
     * Multi-sentence text is more likely to benefit from AI-assisted normalization
     * than very short snippets, so this is used as one of the ambiguity factors in
     * the escalation decision.
     */
    public static function isMultiSentence(string $text): bool
    {
        return preg_match_all('/[.!?]+/', $text) >= 2;
    }
}