<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use TextNormalizer\Config\NormalizerConfig;
use TextNormalizer\Factory\TextNormalizerFactory;

$openAiApiKey = '<YOUR_OPENAI_API_KEY>';

if ($openAiApiKey === '' || $openAiApiKey === '<YOUR_OPENAI_API_KEY>') {
    fwrite(STDERR, "Please set your OpenAI API key in examples/collection.php before running this demo." . PHP_EOL);
    exit(1);
}

$psr17Factory = new Psr17Factory();

$config = new NormalizerConfig(
    provider: 'openai',
    useAi: true,
    forceAi: false,
    openAiApiKey: $openAiApiKey,
    openAiModel: 'gpt-4o-mini',
    minAiLength: 120,
    minAmbiguityFactors: 2,
    maxLengthDeltaRatio: 0.35,
);

$normalizer = TextNormalizerFactory::fromConfig(
    config: $config,
    httpClient: new Client(['timeout' => 30]),
    requestFactory: $psr17Factory,
    streamFactory: $psr17Factory,
);

$texts = [
    101 => "My Esteem d Sister, I Take This Occasn To Inform You That I Recd. Your Last Of The 9th Ult., Though Much Of It Was So Faint & Blur d In The Fold As To Be Scarccly Legible, Esply. Where You Spoke Of Aunt Deborah S Illness And The Matter Of The Lease Near Mill Creek.",
    102 => "We Came On To Harpers Ferry By Way Of Martinsbg. Under A Cold Rain, And Lodg d At The House Of Mrs. Bell, Who Says The Old Genl. Harwood Was Seen There On Tuesdy Last, Altho Some Maintain It Was His Nephew, Mr. H. L. Harrod, The Names Being Much Confus d In The Regster.",
    103 => "I Was Yesterday At St. Matthew s Yard, Where A Stone, Half Sunken & Blacken d, Bore What I Took To Be The Name Of Edwd. L. Farnham, Yet The Sexton Read It As E. T. Farring, And Could Not Resolve The Date Whether 1817 Or 1847, The Figure Being Cloven Quite Away.",
    104 => "There Is Likewise Much Talk Of A Packet Mis-sent To the Office At Fredk., Marked For Dr. Elias Wren, Containing Notes On Qu1nine, Lmp Oyl, Astron. Glass, & Sundry Instruments, But The Clerk S Memorandm Is So Torn At The Edge That No Man Can Say If The Sum Enter d Was 71 Dollars, 7l, Or 7?.",
    105 => "Pray Tell Cousin Marian I Have Not Forgot Her Desire For The Little Volume Of Cowper, Though The Bookseller In Georgetn. Had Only A Worn Copy Wanting Its Title Page. I Remain Yr. Affecte Brother, Jos. C. Wetherell.",
];

// the same context clues are used for all items
$context = [
	'protected_phrases' => [
	    "Mill Creek",
	    "Harpers Ferry",
	    "Martinsburg",
	    "St. Matthew's Yard",
	    "Georgetown",
	],
	'acronyms' => [],    
];

$collectionResult = $normalizer->normalizeCollection(
    texts: $texts,
    context: $context,
);

echo "NORMALIZED VALUES:" . PHP_EOL;
foreach ($collectionResult->normalizedValues() as $key => $normalizedText) {
    echo PHP_EOL . "[{$key}]" . PHP_EOL;
    echo $normalizedText . PHP_EOL;
}

echo PHP_EOL . str_repeat('=', 60) . PHP_EOL;
echo "DETAILED RESULTS:" . PHP_EOL;

foreach ($collectionResult->results() as $key => $result) {
    echo PHP_EOL . "[{$key}]" . PHP_EOL;
    echo 'Used AI: ' . ($result->usedAi() ? 'Yes' : 'No') . PHP_EOL;
    echo 'Model: ' . ($result->model() ?? '(none)') . PHP_EOL;
    echo 'Reason: ' . $result->reason() . PHP_EOL;

    $factors = $result->factors();
    if ($factors !== []) {
        echo 'Factors: ' . implode(', ', $factors) . PHP_EOL;
    }

    echo 'Original: ' . $result->original() . PHP_EOL;
    echo 'Normalized: ' . $result->normalized() . PHP_EOL;
}