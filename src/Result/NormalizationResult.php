<?php

declare(strict_types=1);

namespace TextNormalizer\Result;

final class NormalizationResult
{
    /**
     * @param list<string> $factors
     */
    public function __construct(
        private readonly string $original,
        private readonly string $normalized,
        private readonly bool $usedAi,
        private readonly ?string $model,
        private readonly string $reason,
        private readonly array $factors = [],
    ) {
    }

    public function original(): string
    {
        return $this->original;
    }

    public function normalized(): string
    {
        return $this->normalized;
    }

    public function usedAi(): bool
    {
        return $this->usedAi;
    }

    public function model(): ?string
    {
        return $this->model;
    }

    public function reason(): string
    {
        return $this->reason;
    }

    /**
     * @return list<string>
     */
    public function factors(): array
    {
        return $this->factors;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'original' => $this->original,
            'normalized' => $this->normalized,
            'used_ai' => $this->usedAi,
            'model' => $this->model,
            'reason' => $this->reason,
            'factors' => $this->factors,
        ];
    }
}