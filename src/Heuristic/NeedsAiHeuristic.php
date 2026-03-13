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

        $signals = [];

        if (TextInspector::containsShortUppercaseTokens($original)) {
            $signals[] = 'short_uppercase_tokens';
        }

        if (TextInspector::containsParentheticalAcronymPattern($original)) {
            $signals[] = 'parenthetical_acronym';
        }

        if (TextInspector::containsBusinessJoiners($original)) {
            $signals[] = 'business_joiners';
        }

        if (! empty($context['protected_phrases'])) {
            $signals[] = 'protected_phrases_context';
        }

        if (! empty($context['acronyms'])) {
            $signals[] = 'acronyms_context';
        }

        if (TextInspector::isMultiSentence($original)) {
            $signals[] = 'multi_sentence';
        }

        if (count($signals) < $this->config->minAmbiguitySignals) {
            return new NeedsAiDecision(false, 'Not enough ambiguity signals.', $signals);
        }

        if ($original === $ruleBased) {
            $signals[] = 'rule_based_no_change';
        }

        return new NeedsAiDecision(true, 'Text is damaged and sufficiently ambiguous.', $signals);
    }
}