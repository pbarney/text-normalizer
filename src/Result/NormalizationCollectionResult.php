<?php

declare(strict_types=1);

namespace TextNormalizer\Result;

final class NormalizationCollectionResult
{
    /**
     * @param array<array-key, NormalizationResult> $results
     */
    public function __construct(
        private readonly array $results,
    ) {
    }

    /**
     * @return array<array-key, NormalizationResult>
     */
    public function results(): array
    {
        return $this->results;
    }

    /**
     * @return array<array-key, string>
     */
    public function normalizedValues(): array
    {
        $output = [];

        foreach ($this->results as $key => $result) {
            $output[$key] = $result->normalized();
        }

        return $output;
    }

    /**
     * @return array<array-key, array<string, mixed>>
     */
    public function toArray(): array
    {
        $output = [];

        foreach ($this->results as $key => $result) {
            $output[$key] = $result->toArray();
        }

        return $output;
    }

    /**
     * @return array<array-key, string>
     */
    public function preserveKeys(): array
    {
        return $this->normalizedValues();
    }
}