<?php

declare(strict_types=1);

namespace TextNormalizer\Factory;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use TextNormalizer\Config\NormalizerConfig;
use TextNormalizer\Contract\TextNormalizerInterface;
use TextNormalizer\Exception\UnsupportedProviderException;
use TextNormalizer\Heuristic\NeedsAiHeuristic;
use TextNormalizer\Hybrid\HybridTextNormalizer;
use TextNormalizer\OpenAI\OpenAiAiNormalizer;
use TextNormalizer\OpenAI\OpenAiOutputParser;
use TextNormalizer\OpenAI\OpenAiResponsesClient;
use TextNormalizer\Preprocess\RuleBasedPreprocessor;
use TextNormalizer\Support\Env;
use TextNormalizer\Validator\OutputValidator;

final class TextNormalizerFactory
{
    public static function fromEnvironment(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
    ): TextNormalizerInterface {
        $config = new NormalizerConfig(
            provider: Env::get('TEXT_NORMALIZER_PROVIDER', 'openai') ?? 'openai',
            useAi: Env::bool('TEXT_NORMALIZER_USE_AI', true),
            forceAi: Env::bool('TEXT_NORMALIZER_FORCE_AI', false),
            openAiApiKey: Env::get('OPENAI_API_KEY', '') ?? '',
            openAiModel: Env::get('TEXT_NORMALIZER_OPENAI_MODEL', 'gpt-4o-mini') ?? 'gpt-4o-mini',
            minAiLength: Env::int('TEXT_NORMALIZER_MIN_AI_LENGTH', 120),
            minAmbiguitySignals: Env::int('TEXT_NORMALIZER_MIN_AMBIGUITY_SIGNALS', 2),
            maxLengthDeltaRatio: Env::float('TEXT_NORMALIZER_MAX_LENGTH_DELTA_RATIO', 0.35),
        );

        return self::fromConfig($config, $httpClient, $requestFactory, $streamFactory);
    }

    public static function fromConfig(
        NormalizerConfig $config,
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
    ): TextNormalizerInterface {
        $preprocessor = new RuleBasedPreprocessor();
        $heuristic = new NeedsAiHeuristic($config);
        $validator = new OutputValidator($config);

        return match ($config->provider) {
            'openai' => new HybridTextNormalizer(
                preprocessor: $preprocessor,
                heuristic: $heuristic,
                validator: $validator,
                aiNormalizer: new OpenAiAiNormalizer(
                    client: new OpenAiResponsesClient(
                        httpClient: $httpClient,
                        requestFactory: $requestFactory,
                        streamFactory: $streamFactory,
                        parser: new OpenAiOutputParser(),
                        apiKey: $config->openAiApiKey,
                    ),
                    model: $config->openAiModel,
                ),
            ),
            default => throw new UnsupportedProviderException(
                sprintf('Unsupported provider "%s".', $config->provider)
            ),
        };
    }
}