<?php

declare(strict_types=1);

namespace TextNormalizer\Tests;

use PHPUnit\Framework\TestCase;
use TextNormalizer\Config\NormalizerConfig;
use TextNormalizer\Heuristic\NeedsAiHeuristic;

final class NeedsAiHeuristicTest extends TestCase
{
    public function testShortTextDoesNotUseAi(): void
    {
        $heuristic = new NeedsAiHeuristic(new NormalizerConfig(minAiLength: 200));

        $decision = $heuristic->decide(
            'This Is Damaged Title Case.',
            'This is damaged title case.'
        );

        self::assertFalse($decision->shouldUseAi);
    }

    public function testAmbiguousLongTitleCaseTextUsesAi(): void
    {
        $heuristic = new NeedsAiHeuristic(new NormalizerConfig(minAiLength: 20, minAmbiguityFactors: 2));

        $decision = $heuristic->decide(
            'Whether You Need A Building Demolished OR Want To Rent A Dumpster For A Cleanup, Fancy Pants Excavating Is The Company For You.',
            'Whether you need a building demolished or want to rent a dumpster for a cleanup, Fancy Pants Excavating is the company for you.',
            [
                'protected_phrases' => ['Fancy Pants Excavating'],
                'acronyms' => ['MADD'],
            ],
        );

        self::assertTrue($decision->shouldUseAi);
        self::assertContains('title_case_damage', $decision->factors);
        self::assertContains('protected_phrases_context', $decision->factors);
    }

    public function testOcrStyleDamageCanUseAiWithoutTitleCaseDamage(): void
    {
        $heuristic = new NeedsAiHeuristic(new NormalizerConfig(minAiLength: 20, minAmbiguityFactors: 2));

        $original = 'The clerk s memorandum mentions qu1nine and states the sum was 4?0 dollars. It was entered in Washington.';
        $ruleBased = 'The clerk s memorandum mentions qu1nine and states the sum was 4?0 dollars. It was entered in Washington.';

        $decision = $heuristic->decide(
            $original,
            $ruleBased,
            [
                'protected_phrases' => ['Washington'],
            ],
        );

        self::assertTrue($decision->shouldUseAi);
        self::assertContains('digit_letter_confusion', $decision->factors);
        self::assertContains('broken_possessive_or_contraction', $decision->factors);
        self::assertContains('uncertain_numeric_artifact', $decision->factors);
    }

    public function testSingleOcrDamageFactorDoesNotPassDamageGate(): void
    {
        $heuristic = new NeedsAiHeuristic(new NormalizerConfig(minAiLength: 20, minAmbiguityFactors: 2));

        $original = 'The memorandum mentions qu1nine in Washington. It was later filed properly.';
        $ruleBased = 'The memorandum mentions qu1nine in Washington. It was later filed properly.';

        $decision = $heuristic->decide(
            $original,
            $ruleBased,
            [
                'protected_phrases' => ['Washington'],
            ],
        );

        self::assertFalse($decision->shouldUseAi);
        self::assertSame('Text does not appear sufficiently damaged.', $decision->reason);
        self::assertContains('digit_letter_confusion', $decision->factors);
    }
}