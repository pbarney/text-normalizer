<?php

declare(strict_types=1);

namespace TextNormalizer\Tests;

use PHPUnit\Framework\TestCase;
use TextNormalizer\Preprocess\RuleBasedPreprocessor;

final class RuleBasedPreprocessorTest extends TestCase
{
    public function testNormalizesBasicSentenceCase(): void
    {
        $preprocessor = new RuleBasedPreprocessor();

        $result = $preprocessor->normalize('HELLO WORLD. THIS IS A TEST.');

        self::assertSame('Hello world. This is a test.', $result);
    }

    public function testRestoresKnownAcronymsAndProtectedContextPhrases(): void
    {
        $preprocessor = new RuleBasedPreprocessor();

        $result = $preprocessor->normalize(
            'Fancy Pants hauling & dumpster rental supports madd.',
            [
                'protected_phrases' => 'Fancy Pants Hauling & Dumpster Rental',
                'acronyms' => ['MADD'],
            ],
        );

        self::assertSame('Fancy Pants Hauling & Dumpster Rental supports MADD.', $result);
    }
}