<?php

declare(strict_types=1);

namespace TextNormalizer\Tests;

use PHPUnit\Framework\TestCase;
use TextNormalizer\Result\NormalizationCollectionResult;
use TextNormalizer\Result\NormalizationResult;

final class NormalizationCollectionResultTest extends TestCase
{
    public function testNormalizedValuesPreserveKeys(): void
    {
        $collection = new NormalizationCollectionResult([
            'a' => new NormalizationResult('One', 'one', false, null, 'rule'),
            'b' => new NormalizationResult('Two', 'two', false, null, 'rule'),
        ]);

        self::assertSame([
            'a' => 'one',
            'b' => 'two',
        ], $collection->normalizedValues());
    }
}