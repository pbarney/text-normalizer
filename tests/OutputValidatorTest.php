<?php

declare(strict_types=1);

namespace TextNormalizer\Tests;

use PHPUnit\Framework\TestCase;
use TextNormalizer\Config\NormalizerConfig;
use TextNormalizer\Exception\ValidationException;
use TextNormalizer\Validator\OutputValidator;

final class OutputValidatorTest extends TestCase
{
    public function testRejectsExcessiveLengthDelta(): void
    {
        $validator = new OutputValidator(new NormalizerConfig(maxLengthDeltaRatio: 0.10));

        $this->expectException(ValidationException::class);

        $validator->validate('Short text.', 'This output is much longer than the original text and should fail.');
    }

    public function testAllowsReasonablePlainTextOutput(): void
    {
        $validator = new OutputValidator(new NormalizerConfig());

        $result = $validator->validate('HELLO WORLD.', 'Hello world.');

        self::assertSame('Hello world.', $result);
    }
}