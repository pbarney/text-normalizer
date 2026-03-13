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
    My Esteem d Sister, I Take This Occasn To Inform You That I Recd. Your Last Of The 9th Ult., Though Much Of It Was So Faint & Blur d In The Fold As To Be Scarccly Legible, Esply. Where You Spoke Of Aunt Deborah S Illness And The Matter Of The Lease Near Mill Creek. We Came On To Harpers Ferry By Way Of Martinsbg. Under A Cold Rain, And Lodg d At The House Of Mrs. Bell, Who Says The Old Genl. Harwood Was Seen There On Tuesdy Last, Altho Some Maintain It Was His Nephew, Mr. H. L. Harrod, The Names Being Much Confus d In The Regster. I Was Yesterday At St. Matthew s Yard, Where A Stone, Half Sunken & Blacken d, Bore What I Took To Be The Name Of Edwd. L. Farnham, Yet The Sexton Read It As E. T. Farring, And Could Not Resolve The Date Whether 1817 Or 1847, The Figure Being Cloven Quite Away. There Is Likewise Much Talk Of A Packet Mis-sent To the Office At Fredk., Marked For Dr. Elias Wren, Containing Notes On Qu1nine, Lmp Oyl, Astron. Glass, & Sundry Instruments, But The Clerk S Memorandm Is So Torn At The Edge That No Man Can Say If The Sum Enter d Was 71 Dollars, 7l, Or 7?. Pray Tell Cousin Marian I Have Not Forgot Her Desire For The Little Volume Of Cowper, Though The Bookseller In Georgetn. Had Only A Worn Copy Wanting Its Title Page. I Remain Yr. Affecte Brother, Jos. C. Wetherell.
    TEXT;

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

$result = $normalizer->normalize(
    text: $text,
    context: $context,
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