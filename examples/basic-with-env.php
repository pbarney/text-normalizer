<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use TextNormalizer\Factory\TextNormalizerFactory;
use Dotenv\Dotenv;

// Load the environment variables (OPENAI_API_KEY, etc) from the environment
// Make sure you've renamed `.env.example` to `.env` and added your OPENAI_API_KEY to it.
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
try {
$dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    echo $e->getMessage() . PHP_EOL;
    exit(1);
}

$psr17Factory = new Psr17Factory();

$normalizer = TextNormalizerFactory::fromEnvironment(
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

$factors = $result->factors();

if ($factors !== []) {
    echo PHP_EOL . "FACTORS:" . PHP_EOL;

    foreach ($factors as $factor) {
        echo "- {$factor}" . PHP_EOL;
    }
}