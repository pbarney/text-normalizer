<?php

declare(strict_types=1);

namespace TextNormalizer\Heuristic;

use TextNormalizer\Config\NormalizerConfig;
use TextNormalizer\Support\TextInspector;

final class NeedsAiHeuristic
{
    public function __construct(
        private readonly NormalizerConfig $config,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function decide(string $original, string $ruleBased, array $context = []): NeedsAiDecision
    {
        if ($this->config->forceAi) {
            return new NeedsAiDecision(true, 'AI forced by configuration.', ['force_ai']);
        }

        if (! $this->config->useAi) {
            return new NeedsAiDecision(false, 'AI disabled by configuration.');
        }

        if (mb_strlen($original) < $this->config->minAiLength) {
            return new NeedsAiDecision(false, 'Text shorter than AI threshold.');
        }

        $damageFactors = [];
        $ocrDamageFactors = [];
        $ambiguityFactors = [];

        if (TextInspector::looksDamagedTitleCase($original)) {
            $damageFactors[] = 'title_case_damage';
        }

        if (TextInspector::containsDigitLetterConfusion($original)) {
            $damageFactors[] = 'digit_letter_confusion';
            $ocrDamageFactors[] = 'digit_letter_confusion';
        }

        if (TextInspector::containsBrokenPossessiveOrContraction($original)) {
            $damageFactors[] = 'broken_possessive_or_contraction';
            $ocrDamageFactors[] = 'broken_possessive_or_contraction';
        }

        if (TextInspector::containsSplitWordArtifact($original)) {
            $damageFactors[] = 'split_word_artifact';
            $ocrDamageFactors[] = 'split_word_artifact';
        }

        if (TextInspector::containsUncertainNumericArtifact($original)) {
            $damageFactors[] = 'uncertain_numeric_artifact';
            $ocrDamageFactors[] = 'uncertain_numeric_artifact';
        }

        $hasSufficientDamage =
            in_array('title_case_damage', $damageFactors, true)
            || count($ocrDamageFactors) >= 2;

        if (! $hasSufficientDamage) {
            return new NeedsAiDecision(
                false,
                'Text does not appear sufficiently damaged.',
                $damageFactors,
            );
        }

        if (TextInspector::containsShortUppercaseTokens($original)) {
            $ambiguityFactors[] = 'short_uppercase_tokens';
        }

        if (TextInspector::containsParentheticalAcronymPattern($original)) {
            $ambiguityFactors[] = 'parenthetical_acronym';
        }

        if (TextInspector::containsBusinessJoiners($original)) {
            $ambiguityFactors[] = 'business_joiners';
        }

        if (! empty($context['protected_phrases'])) {
            $ambiguityFactors[] = 'protected_phrases_context';
        }

        if (! empty($context['acronyms'])) {
            $ambiguityFactors[] = 'acronyms_context';
        }

        if (TextInspector::isMultiSentence($original)) {
            $ambiguityFactors[] = 'multi_sentence';
        }

        $factors = array_values(array_unique([...$damageFactors, ...$ambiguityFactors]));

        if (count($ambiguityFactors) < $this->config->minAmbiguityFactors) {
            return new NeedsAiDecision(false, 'Not enough ambiguity factors.', $factors);
        }

        if ($original === $ruleBased) {
            $factors[] = 'rule_based_no_change';
        }

        return new NeedsAiDecision(true, 'Text is damaged and sufficiently ambiguous.', $factors);
    }
}