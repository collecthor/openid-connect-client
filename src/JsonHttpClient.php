<?php

declare(strict_types=1);

namespace Collecthor\OpenidConnectClient;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * This adapter wraps any standard PSR Http Client to provide a simple interface for our OIDC client for retrieving
 * JSON data
 *
 * @psalm-api
 */
class JsonHttpClient
{
    public function __construct(private readonly \Psr\Http\Client\ClientInterface $client, private readonly RequestFactoryInterface $requestFactory)
    {
    }

    /**
     * @param string|UriInterface $uri
     * @return array<mixed>
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function getJsonArray(string|UriInterface $uri): array
    {
        $response = $this->client->sendRequest($this->requestFactory->createRequest('GET', $uri));
        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Request failed, got status code: {$response->getStatusCode()}");
        }
        if (!str_starts_with($response->getHeaderLine('Content-Type'), 'application/json')) {
            throw new \Exception("Unexpected content type, got: {$response->getHeaderLine('Content-Type')}");
        }

        $result = json_decode($response->getBody()->getContents(), true, JSON_THROW_ON_ERROR);
        if (!is_array($result)) {
            throw new \Exception('Unexpected JSON value');
        }
        return $result;
    }
}
