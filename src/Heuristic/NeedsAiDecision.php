<?php

declare(strict_types=1);

namespace TextNormalizer\Heuristic;

final class NeedsAiDecision
{
    /**
     * @param list<string> $signals
     */
    public function __construct(
        public readonly bool $shouldUseAi,
        public readonly string $reason,
        public readonly array $signals = [],
    ) {
    }
}