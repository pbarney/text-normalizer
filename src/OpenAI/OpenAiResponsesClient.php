<?php

declare(strict_types=1);

namespace TextNormalizer\OpenAI;

use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use TextNormalizer\Exception\ApiException;

final class OpenAiResponsesClient
{
    private const ENDPOINT = 'https://api.openai.com/v1/responses';

    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly OpenAiOutputParser $parser,
        private readonly string $apiKey,
    ) {
    }

    public function normalizeText(string $model, string $prompt): string
    {
        if (trim($this->apiKey) === '') {
            throw new ApiException('Missing OpenAI API key.');
        }

        $payload = [
            'model' => $model,
            'input' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => $prompt,
                        ],
                    ],
                ],
            ],
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'normalized_text_response',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'normalized_text' => [
                                'type' => 'string',
                            ],
                        ],
                        'required' => ['normalized_text'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ];

        try {
            $json = json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new ApiException('Failed to encode OpenAI request payload.', previous: $e);
        }

        $request = $this->requestFactory->createRequest('POST', self::ENDPOINT)
            ->withHeader('Authorization', 'Bearer ' . $this->apiKey)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($this->streamFactory->createStream($json));

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new ApiException('OpenAI HTTP request failed.', previous: $e);
        }

        $status = $response->getStatusCode();
        $contents = (string) $response->getBody();

        if ($status < 200 || $status >= 300) {
            throw new ApiException('OpenAI API request failed with status ' . $status . ': ' . $contents);
        }

        try {
            $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new ApiException('OpenAI API returned invalid JSON.', previous: $e);
        }

        if (! is_array($data)) {
            throw new ApiException('OpenAI API returned an unexpected JSON structure.');
        }

        $responseStatus = $data['status'] ?? null;
        if (! is_string($responseStatus)) {
            throw new ApiException('OpenAI API response did not include a valid status.');
        }

        if ($responseStatus !== 'completed') {
            throw new ApiException('OpenAI API response status was not completed: ' . $responseStatus);
        }

        return $this->parser->extractNormalizedText($data);
    }
}