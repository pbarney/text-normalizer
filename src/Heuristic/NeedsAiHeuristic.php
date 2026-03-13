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

        if (! TextInspector::looksDamagedTitleCase($original)) {
            return new NeedsAiDecision(false, 'Text does not appear sufficiently damaged.');
        }

        $factors = [];

        if (TextInspector::containsShortUppercaseTokens($original)) {
            $factors[] = 'short_uppercase_tokens';
        }

        if (TextInspector::containsParentheticalAcronymPattern($original)) {
            $factors[] = 'parenthetical_acronym';
        }

        if (TextInspector::containsBusinessJoiners($original)) {
            $factors[] = 'business_joiners';
        }

        if (! empty($context['protected_phrases'])) {
            $factors[] = 'protected_phrases_context';
        }

        if (! empty($context['acronyms'])) {
            $factors[] = 'acronyms_context';
        }

        if (TextInspector::isMultiSentence($original)) {
            $factors[] = 'multi_sentence';
        }

        if (count($factors) < $this->config->minAmbiguityFactors) {
            return new NeedsAiDecision(false, 'Not enough ambiguity factors.', $factors);
        }

        if ($original === $ruleBased) {
            $factors[] = 'rule_based_no_change';
        }

        return new NeedsAiDecision(true, 'Text is damaged and sufficiently ambiguous.', $factors);
    }
}