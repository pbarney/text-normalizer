<?php

declare(strict_types=1);

namespace TextNormalizer\Contract;

use TextNormalizer\Result\AiNormalizationResponse;

interface AiNormalizerInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function normalize(string $text, array $context = []): AiNormalizationResponse;
}