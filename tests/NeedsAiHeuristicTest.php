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

    public function testAmbiguousLongTextUsesAi(): void
    {
        $heuristic = new NeedsAiHeuristic(new NormalizerConfig(minAiLength: 20, minAmbiguitySignals: 2));

        $decision = $heuristic->decide(
            'Whether You Need A Building Demolished OR Want To Rent A Dumpster For A Cleanup, Fancy Pants Hauling & Dumpster Rental Is The Company For You.',
            'Whether you need a building demolished or want to rent a dumpster for a cleanup, Fancy Pants Hauling & Dumpster Rental is the company for you.',
            [
                'company_name' => 'Fancy Pants Hauling & Dumpster Rental',
                'acronyms' => ['MADD'],
            ],
        );

        self::assertTrue($decision->shouldUseAi);
        self::assertContains('company_name_context', $decision->signals);
    }
}