# Text Normalizer

A configurable PHP 8.2+ package for normalizing damaged or poorly capitalized text, using AI only when necessary.

**It supports:**

- single-string normalization
- collection normalization
- deterministic preprocessing
- optional AI escalation only when heuristics say it is needed
- OpenAI via the Responses API (Interfaces are available if you want to write your own provider adapters, such as Anthropic or Grok)
- environment-driven configuration
- rich result objects with convenience methods

## Examples

### Example 1: A poorly-OCR'd mid-19th century person correspondence

**Input text:**

> My Esteem d Sister, I Take This Occasn To Inform You That I Recd. Your Last Of The 9th Ult., Though Much Of It Was So Faint & Blur d In The Fold As To Be Scarccly Legible, Esply. Where You Spoke Of Aunt Deborah S Illness And The Matter Of The Lease Near Mill Creek. We Came On To Harpers Ferry By Way Of Martinsbg. Under A Cold Rain, And Lodg d At The House Of Mrs. Bell, Who Says The Old Genl. Harwood Was Seen There On Tuesdy Last, Altho Some Maintain It Was His Nephew, Mr. H. L. Harrod, The Names Being Much Confus d In The Regster. I Was Yesterday At St. Matthew s Yard, Where A Stone, Half Sunken & Blacken d, Bore What I Took To Be The Name Of Edwd. L. Farnham, Yet The Sexton Read It As E. T. Farring, And Could Not Resolve The Date Whether 1817 Or 1847, The Figure Being Cloven Quite Away. There Is Likewise Much Talk Of A Packet Mis-sent To the Office At Fredk., Marked For Dr. Elias Wren, Containing Notes On Qu1nine, Lmp Oyl, Astron. Glass, & Sundry Instruments, But The Clerk S Memorandm Is So Torn At The Edge That No Man Can Say If The Sum Enter d Was 71 Dollars, 7l, Or 7?. Pray Tell Cousin Marian I Have Not Forgot Her Desire For The Little Volume Of Cowper, Though The Bookseller In Georgetn. Had Only A Worn Copy Wanting Its Title Page. I Remain Yr. Affecte Brother, Jos. C. Wetherell.

**Normalized output (using no contextual clues)**

> My Esteemed Sister, I take this occasion to inform you that I received your last of the 9th ult., though much of it was so faint and blurred in the fold as to be scarcely legible, especially where you spoke of Aunt Deborah's illness and the matter of the lease near Mill Creek. We came on to Harpers Ferry by way of Martinsburg under a cold rain, and lodged at the house of Mrs. Bell, who says the old General Harwood was seen there on Tuesday last, although some maintain it was his nephew, Mr. H. L. Harrod, the names being much confused in the register. I was yesterday at St. Matthew's Yard, where a stone, half sunken and blackened, bore what I took to be the name of Edward L. Farnham, yet the sexton read it as E. T. Farring, and could not resolve the date whether 1817 or 1847, the figure being cloven quite away. There is likewise much talk of a packet missent to the office at Frederick, marked for Dr. Elias Wren, containing notes on quinine, lamp oil, astronomical glass, and sundry instruments, but the clerk's memorandum is so torn at the edge that no man can say if the sum entered was 71 dollars, 71, or 7?. Pray tell Cousin Marian I have not forgot her desire for the little volume of Cowper, though the bookseller in Georgetown had only a worn copy wanting its title page. I remain your affectionate brother, Jos. C. Wetherell.

### Example 2: A heavily damaged late-18th century ship's log

**Input text:**

> At 4 Bells In The Forenoon Watch, Lat. Suppos d 3I° 4O' N., Long. By Dead Reckng. 72° I5' W., We Made Poor Headway Under Reef d Tops ls, The Wind Hauling Sudden ly To E.N.E. With A Cross Sea That Struck The Larbd. Quarter So Hard As To Stove One Of The Quarter-Boats And Carry Away Part Of The Rail Near Capt. Wetherby S Companion. Mr. Elias Boone, 1st Mate, Reports The Lead Gave No Bottom At 85 Fathms., Though The Previous Nights Entry Seems To Mark Soundings At 45, Which I Suspect To Be A Clerks Error, The Figure 8 Having Been Mistook For 4 In More Places Than One. At Noon We Sighted, Or Thought We Sighted, A Low Dark Line To The S.S.W., Which Old Jenks Took For Cape Hatteras, But Mr. Boone Maintaind It Was No More Than Fog Bank & Broken Light. Issued To The Hands Salt Beef, Biscuit, & A Gill Of Rum Per Man, Save For Young T. Mercer, Laid Up Since Yestdy. With A Cramp In The Chest After Going Aloft In Rain. The Surgeons Memorandm Mentions Jesuits Bark, Lamp Oyl, & Vinegr., But The Purser S List Is So Blurr d At The Fold That I Cannot Tell Whether The Remaining Flour Is 17 Bbls. Or 7I. At Dog Watch A Small Packet, Mark d For The Admiralty Office At Norfolk, Was Found Damp Through, Its Seal Half Gone, And The Name Upon It Read By One Hand As Lt. Horatio Vale, By Another As H. Yale.

**Normalized output (using no contextual clues)**

> At 4 bells in the forenoon watch, lat. suppos’d 31° 40' N., long. by dead reckoning 72° 15' W., we made poor headway under reefed topsails, the wind hauling suddenly to E.N.E. with a cross sea that struck the larboard quarter so hard as to stove one of the quarter-boats and carry away part of the rail near Capt. Wetherby's companion, Mr. Elias Boone, 1st mate. Reports the lead gave no bottom at 85 fathoms, though the previous night's entry seems to mark soundings at 45, which I suspect to be a clerk's error, the figure 8 having been mistook for 4 in more places than one. At noon, we sighted, or thought we sighted, a low dark line to the S.S.W., which Old Jenks took for Cape Hatteras, but Mr. Boone maintained it was no more than a fog bank and broken light. Issued to the hands salt beef, biscuit, and a gill of rum per man, save for young T. Mercer, laid up since yesterday with a cramp in the chest after going aloft in rain. The surgeon's memorandum mentions Jesuit's bark, lamp oil, and vinegar, but the purser's list is so blurred at the fold that I cannot tell whether the remaining flour is 17 bbls. or 71. At dog watch, a small packet, marked for the Admiralty Office at Norfolk, was found damp through, its seal half gone, and the name upon it read by one hand as Lt. Horatio Vale, by another as H. Yale.

Contextual clues are known difficult words, usually proper names of people and locations, or acronyms that may not be obivious from the inferred context.

## Installation

```bash
composer require pbarney/text-normalizer
```

The package is transport-agnostic, so you must also provide:

* a PSR-18 HTTP client
* a PSR-17 HTTP request factory
* a PSR-17 HTTP stream factory

**One simple stack is:**

```bash
composer require guzzlehttp/guzzle nyholm/psr7
```

If your app already has equivalent PSR-18 and PSR-17 implementations, you can skip this step.

## Environment-based usage

If your application already provides environment variables, you may use `TextNormalizerFactory::fromEnvironment(...)`.

### Loading `.env` files

The package reads environment variables, but it does not load `.env` files itself. One common way to enable this is to install the popular `vlucas/phpdotenv`, which you can add as follows:

```bash
composer require vlucas/phpdotenv
```

### Environment variables

```dotenv
OPENAI_API_KEY=your_api_key_here
TEXT_NORMALIZER_OPENAI_MODEL=gpt-4o-mini
TEXT_NORMALIZER_PROVIDER=openai
TEXT_NORMALIZER_USE_AI=true
TEXT_NORMALIZER_FORCE_AI=false
TEXT_NORMALIZER_MIN_AI_LENGTH=120
TEXT_NORMALIZER_MIN_AMBIGUITY_FACTORS=2
TEXT_NORMALIZER_MAX_LENGTH_DELTA_RATIO=0.35
```

## Demos

### Demo Requirements

As mentioned earlier, **this package requires PSR-17 and PSR-18 implementations.** With this in mind, the instructions for each demo includes them.

You will also need an OpenAI API key (typically from `https://auth.openai.com/`).

### Basic Demo (`examples/basic.php`)

**Steps:**

1. Run `composer require guzzlehttp/guzzle nyholm/psr7` to install PSR-17 and PSR-18 dependencies.
2. Edit `examples/basic.php` and replace the API key placeholder with your own OpenAI API key.
3. Run the script with `php examples/basic.php`

### Loading `.env`

The included demo `examples/basic-with-env.php` requires `vlucas/phpdotenv` to read from a `.env` file. 

1. If you haven't already, run `composer require guzzlehttp/guzzle nyholm/psr7` to install PSR-17 and PSR-18 dependencies.
2. Run `composer require vlucas/phpdotenv` to install `.env` file support.
3. Rename `.env.example` to `.env` and edit it to include your `OPENAI_API_KEY`.
4. Run `php examples/basic-with-env.php`

## Usage

### Basic Usage

For a full, working example, see `examples/basic.php`.

In brief:

```php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use TextNormalizer\Config\NormalizerConfig;
use TextNormalizer\Factory\TextNormalizerFactory;

$openAiApiKey = '<YOUR_OPENAI_API_KEY>';

$psr17Factory = new Psr17Factory();

$normalizer = TextNormalizerFactory::fromConfig(
    config: new NormalizerConfig(
        provider: 'openai',
        useAi: true,
        forceAi: false,
        openAiApiKey: $openAiApiKey,
        openAiModel: 'gpt-4o-mini',
        minAiLength: 120,
        minAmbiguityFactors: 2,
        maxLengthDeltaRatio: 0.35,
    ),
    httpClient: new Client(['timeout' => 30]),
    requestFactory: $psr17Factory,
    streamFactory: $psr17Factory,
);

$result = $normalizer->normalize(
    text: 'Whether You Need A Building Demolished OR Want To Rent A Dumpster...',
    context: [
        'acronyms' => ['MADD'],
        'protected_phrases' => ['Fancy Pants Hauling & Dumpster Rental', 'Topeka', 'Kansas'],
    ],
);

echo $result->normalized();
```

### Basic Usage Utilizing a `.env` configuration file

```php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use TextNormalizer\Factory\TextNormalizerFactory;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$psr17Factory = new Psr17Factory();

$normalizer = TextNormalizerFactory::fromEnvironment(
    httpClient: new Client(['timeout' => 30]),
    requestFactory: $psr17Factory,
    streamFactory: $psr17Factory,
);

$result = $normalizer->normalize(
    text: 'Whether You Need A Building Demolished OR Want To Rent A Dumpster...',
    context: [
        'acronyms' => ['MADD'],
        'protected_phrases' => ['Fancy Pants Hauling & Dumpster Rental', 'Topeka', 'Kansas'],
    ],
);

echo $result->normalized();
```

For a full example, see `examples/basic-with-env.php`.

### Collection usage

The package supports bulk-loading of text samples via the `$normalizer->normalizeCollection()` method.

The usage is similar to the single-string example, but instead of calling `$normalizer->normalize()` with a single `text` string, you call `$normalizer->normalizeCollection()` with an iterable of strings (e.g., an `array`) passed as `texts`.

One note: `normalizeCollection()` applies the same context array to every string in the collection, so it works best when all items share the same context or when the items don't need much context. If each item needs its own context settings, then you should loop through them and call normalize() on each item instead.

```php
$collectionResult = $normalizer->normalizeCollection(
    texts: [
        101 => 'Fancy Pants Excavating Provides A Variety Of Home And Garden Services. The Company Is Located IN Topeka, Kansas. We support MADD.',
        102 => 'Fancy Pants Excavating Offers Flatworks For Sidewalks, Driveways, Patios, Garage Floors And Porches. The Company Also Provides Power Washing And Sealing Concrete.',
        103 => 'Fancy Pants Excavating S Project Managers Help To Review And Permit The Plans. Its Concrete Foundation Walls Are Resistant To Fire, Wind, Insects, Decay, Mold And Efflorescence.',
    ],
    context: [
        'acronyms' => ['MADD'],
        'protected_phrases' => ['Fancy Pants Hauling & Dumpster Rental', 'Topeka', 'Kansas'],
    ],
);

foreach ($collectionResult->normalizedValues() as $key => $normalizedText) {
    echo PHP_EOL . "[{$key}]" . PHP_EOL;
    echo $normalizedText . PHP_EOL;
}
```

For a full example, see `examples/collection.php`.

## Heuristic for AI escalation

The package always runs deterministic preprocessing first.

**AI is used only if:**

- AI is enabled
- the text meets the minimum length
- the text looks sufficiently damaged
- enough ambiguity factors are present

**Ambiguity factors include:**

- suspicious short uppercase tokens such as `US`, `OR`, `IN`
- acronym patterns like `( MADD )`
- context fields (i.e., `protected_phrases`, `acronyms`)
- multi-sentence title-cased text

## Result objects

### `NormalizationResult`

Returned by `$normalizer->normalize()`.

- `normalized()` – Returns the final normalized text.
- `original()` – Returns the original input text before normalization.
- `usedAi()` – Returns `true` if AI was used for the final result, otherwise `false`.
- `model()` – Returns the AI model name used for normalization, or `null` if AI was not used.
- `reason()` – Returns a short explanation of why AI was or was not used, or why the rule-based result was used as a fallback.
- `factors()` – Returns the heuristic factors that influenced the AI-escalation decision.
- `toArray()` – Returns the full result as an associative array.

### `NormalizationCollectionResult`

Returned by `$normalizer->normalizeCollection()`.

- `results()` – Returns all `NormalizationResult` objects in the collection, preserving their original keys.
- `normalizedValues()` – Returns only the normalized strings for the collection, preserving keys.
- `toArray()` – Returns the full collection result as an array of associative arrays.
- `preserveKeys()` – Returns the normalized strings while explicitly preserving the original collection keys.

## Design notes

- text-normalizer is designed to be provider-agnostic, but it is not "multi-provider" yet. OpenAI is the first AI adapter (so far).
- Invalid AI output falls back to the deterministic non-AI result.
- HTTP timeout configuration is handled by the HTTP client you pass in.
