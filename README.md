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
TEXT_NORMALIZER_MIN_AMBIGUITY_SIGNALS=2
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
        minAmbiguitySignals: 2,
        maxLengthDeltaRatio: 0.35,
    ),
    httpClient: new Client(['timeout' => 30]),
    requestFactory: $psr17Factory,
    streamFactory: $psr17Factory,
);

$result = $normalizer->normalize(
    text: 'Whether You Need A Building Demolished OR Want To Rent A Dumpster...',
    context: [
        'company_name' => 'Fancy Pants Hauling & Dumpster Rental',
        'acronyms' => ['MADD'],
        'proper_phrases' => ['Topeka', 'Kansas', 'MADD'],
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
        'company_name' => 'Fancy Pants Hauling & Dumpster Rental',
        'acronyms' => ['MADD'],
        'proper_phrases' => ['Topeka', 'Kansas', 'MADD'],
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
        'company_name' => 'Fancy Pants Excavating',
        'acronyms' => ['MADD'],
        'proper_phrases' => ['Topeka', 'Kansas'],
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
- enough ambiguity signals are present

**Ambiguity signals include:**

- suspicious short uppercase tokens such as `US`, `OR`, `IN`
- acronym patterns like `( MADD )`
- company-name signals like `&`, `/`, `-`
- context fields such as `company_name`, `proper_phrases`, `acronyms`
- multi-sentence title-cased text

## Result objects

### `NormalizationResult`

Returned by `$normalizer->normalize()`.

- `normalized()` – Returns the final normalized text.
- `original()` – Returns the original input text before normalization.
- `usedAi()` – Returns `true` if AI was used for the final result, otherwise `false`.
- `model()` – Returns the AI model name used for normalization, or `null` if AI was not used.
- `reason()` – Returns a short explanation of why AI was or was not used, or why the rule-based result was used as a fallback.
- `signals()` – Returns the heuristic signals that influenced the AI-escalation decision.
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
