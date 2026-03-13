<?php

declare(strict_types=1);

namespace TextNormalizer\Hybrid;

use TextNormalizer\Contract\AiNormalizerInterface;
use TextNormalizer\Contract\TextNormalizerInterface;
use TextNormalizer\Exception\NormalizationException;
use TextNormalizer\Heuristic\NeedsAiHeuristic;
use TextNormalizer\Preprocess\RuleBasedPreprocessor;
use TextNormalizer\Result\NormalizationCollectionResult;
use TextNormalizer\Result\NormalizationResult;
use TextNormalizer\Validator\OutputValidator;

final class HybridTextNormalizer implements TextNormalizerInterface
{
    public function __construct(
        private readonly RuleBasedPreprocessor $preprocessor,
        private readonly NeedsAiHeuristic $heuristic,
        private readonly OutputValidator $validator,
        private readonly AiNormalizerInterface $aiNormalizer,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function normalize(string $text, array $context = []): NormalizationResult
    {
        $ruleBased = $this->preprocessor->normalize($text, $context);
        $decision = $this->heuristic->decide($text, $ruleBased, $context);

        if (! $decision->shouldUseAi) {
            return new NormalizationResult(
                original: $text,
                normalized: $ruleBased,
                usedAi: false,
                model: null,
                reason: $decision->reason,
                factors: $decision->factors,
            );
        }

        try {
            $response = $this->aiNormalizer->normalize($text, $context);
            $validated = $this->validator->validate($text, $response->normalizedText);

            return new NormalizationResult(
                original: $text,
                normalized: $validated,
                usedAi: true,
                model: $response->model,
                reason: $decision->reason,
                factors: $decision->factors,
            );
        } catch (NormalizationException $e) {
            return new NormalizationResult(
                original: $text,
                normalized: $ruleBased,
                usedAi: false,
                model: null,
                reason: 'AI failed, fell back to rule-based normalization: ' . $e->getMessage(),
                factors: $decision->factors,
            );
        }
    }

    /**
     * @param iterable<array-key, string> $texts
     * @param array<string, mixed> $context
     */
    public function normalizeCollection(iterable $texts, array $context = []): NormalizationCollectionResult
    {
        $results = [];

        foreach ($texts as $key => $text) {
            $results[$key] = $this->normalize($text, $context);
        }

        return new NormalizationCollectionResult($results);
    }
}