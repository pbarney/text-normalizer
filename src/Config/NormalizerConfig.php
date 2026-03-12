<?php

declare(strict_types=1);

namespace TextNormalizer\Config;

final class NormalizerConfig
{
    public function __construct(
        public readonly string $provider = 'openai',
        public readonly bool $useAi = true,
        public readonly bool $forceAi = false,
        public readonly string $openAiApiKey = '',
        public readonly string $openAiModel = 'gpt-4o-mini',
        public readonly int $minAiLength = 120,
        public readonly int $minAmbiguitySignals = 2,
        public readonly float $maxLengthDeltaRatio = 0.35,
    ) {
    }
}