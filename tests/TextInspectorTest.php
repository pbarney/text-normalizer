<?php

declare(strict_types=1);

namespace TextNormalizer\Tests;

use PHPUnit\Framework\TestCase;
use TextNormalizer\Support\TextInspector;

final class TextInspectorTest extends TestCase
{
    public function testDetectsDigitLetterConfusion(): void
    {
        self::assertTrue(TextInspector::containsDigitLetterConfusion('The crate contained qu1nine.'));
    }

    public function testDetectsBrokenPossessiveOrContraction(): void
    {
        self::assertTrue(TextInspector::containsBrokenPossessiveOrContraction('The Company S records were damaged.'));
        self::assertTrue(TextInspector::containsBrokenPossessiveOrContraction('It Wasn T clear who signed it.'));
    }

    public function testDetectsSplitWordArtifact(): void
    {
        self::assertTrue(TextInspector::containsSplitWordArtifact('The clerk arriv d late.'));
        self::assertTrue(TextInspector::containsSplitWordArtifact('The storm came sudden ly.'));
        self::assertTrue(TextInspector::containsSplitWordArtifact('They kept them selves hidden.'));
    }

    public function testDetectsUncertainNumericArtifact(): void
    {
        self::assertTrue(TextInspector::containsUncertainNumericArtifact('The total was listed as 4?0 dollars.'));
    }
}