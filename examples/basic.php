<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use TextNormalizer\Factory\TextNormalizerFactory;
use TextNormalizer\Config\NormalizerConfig;

$openAiApiKey = '<YOUR_OPENAI_API_KEY>';

if ($openAiApiKey === '' || $openAiApiKey === '<YOUR_OPENAI_API_KEY>') {
    fwrite(STDERR, "Please set your OpenAI API key in examples/basic.php before running this demo." . PHP_EOL);
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
    minAmbiguitySignals: 2,
    maxLengthDeltaRatio: 0.35,
);

$normalizer = TextNormalizerFactory::fromConfig(
    config: $config,
    httpClient: new Client(['timeout' => 30]),
    requestFactory: $psr17Factory,
    streamFactory: $psr17Factory,
);

$text = <<<TEXT
    Fancy Pants Excavating Provides A Variety Of Home And Garden Services. The Company Specializes IN Various Concrete Wall Designs Has Ability To Resist Soil And Water Pressure And Construction Loads. Its Concrete Foundation Walls Are Resistant To Fire, Wind, Insects, Decay, Mold And Efflorescence. Fancy Pants Excavating S Walls Are Available IN Several Shapes, Including Curved And Angled. The Company S Project Managers Help To Review And Permit The Plans. It Offers Various Flatworks For Sidewalks, Driveways, Patios, Garage Floors And Porches. Fancy Pants Excavating Also Provides Services To Protect Investment Such As Power Washing And Sealing Concrete. We support MADD. The Company Is Located IN Topeka, Kansas.
    TEXT;

$result = $normalizer->normalize(
    text: $text,
    context: [
        'protected_phrases' => ['Fancy Pants Excavating', 'Topeka', 'Kansas'],
        'acronyms' => ['MADD'],
    ],
);

printf(
    "ORIGINAL:\n%s\n\nNORMALIZED:\n%s\n\nUSED AI: %s\nMODEL: %s\nREASON: %s\n",
    $result->original(),
    $result->normalized(),
    $result->usedAi() ? 'Yes' : 'No',
    $result->model() ?? '(none)',
    $result->reason(),
);

$signals = $result->signals();

if ($signals !== []) {
    echo PHP_EOL . "SIGNALS:" . PHP_EOL;

    foreach ($signals as $signal) {
        echo "- {$signal}" . PHP_EOL;
    }
}