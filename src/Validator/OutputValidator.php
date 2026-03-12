<?php

declare(strict_types=1);

namespace TextNormalizer\Validator;

use TextNormalizer\Config\NormalizerConfig;
use TextNormalizer\Exception\ValidationException;

final class OutputValidator
{
    public function __construct(
        private readonly NormalizerConfig $config,
    ) {
    }

    public function validate(string $original, string $candidate): string
    {
        $candidate = trim($candidate);

        if ($candidate === '') {
            throw new ValidationException('Normalized output is empty.');
        }

        $originalLength = max(1, mb_strlen($original));
        $candidateLength = mb_strlen($candidate);
        $deltaRatio = abs($candidateLength - $originalLength) / $originalLength;

        if ($deltaRatio > $this->config->maxLengthDeltaRatio) {
            throw new ValidationException('Normalized output length delta exceeded configured ratio.');
        }

        if (preg_match('/^[-*]\s/m', $candidate) === 1) {
            throw new ValidationException('Normalized output looks like a list, not plain text.');
        }

        if (preg_match('/^```|```$/m', $candidate) === 1) {
            throw new ValidationException('Normalized output looks like a code block, not plain text.');
        }

        return $candidate;
    }
}