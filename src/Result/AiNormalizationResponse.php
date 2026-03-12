<?php

declare(strict_types=1);

namespace TextNormalizer\Result;

final class AiNormalizationResponse
{
    public function __construct(
        public readonly string $normalizedText,
        public readonly string $model,
    ) {
    }
}