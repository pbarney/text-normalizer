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
    101 => 'Fancy Pants Excavating Provides A Variety Of Home And Garden Services. The Company Is Located IN Topeka, Kansas. We support MADD.',
    102 => 'Fancy Pants Excavating Offers Flatworks For Sidewalks, Driveways, Patios, Garage Floors And Porches. The Company Also Provides Power Washing And Sealing Concrete.',
    103 => 'Fancy Pants Excavating S Project Managers Help To Review And Permit The Plans. Its Concrete Foundation Walls Are Resistant To Fire, Wind, Insects, Decay, Mold And Efflorescence.',
];

$collectionResult = $normalizer->normalizeCollection(
    texts: $texts,
    context: [
        'protected_phrases' => ['Fancy Pants Excavating', 'Topeka', 'Kansas'],
        'acronyms' => ['MADD'],
    ],
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
        echo 'Fators: ' . implode(', ', $factors) . PHP_EOL;
    }

    echo 'Original: ' . $result->original() . PHP_EOL;
    echo 'Normalized: ' . $result->normalized() . PHP_EOL;
}