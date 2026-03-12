<?php

declare(strict_types=1);

namespace TextNormalizer\Contract;

use TextNormalizer\Result\NormalizationCollectionResult;
use TextNormalizer\Result\NormalizationResult;

interface TextNormalizerInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function normalize(string $text, array $context = []): NormalizationResult;

    /**
     * @param iterable<array-key, string> $texts
     * @param array<string, mixed> $context
     */
    public function normalizeCollection(iterable $texts, array $context = []): NormalizationCollectionResult;
}