<?php

declare(strict_types=1);

namespace Collecthor\OpenidConnectClient;

use Collecthor\OpenidConnectClient\models\OpenIdAuthenticationRequest;
use Collecthor\OpenidConnectClient\models\OpenIdAuthenticationResponse;
use Firebase\JWT\JWT;
use Psr\Http\Client\ClientInterface as HttpClient;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * @psalm-api
 */
class Client implements ClientInterface
{
    public function __construct(
        private readonly UriFactoryInterface $uriFactory,
        private readonly JsonHttpClient $httpClient,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly RequestFactoryInterface $requestFactory
    ) {
    }

    private function getConfiguration(UriInterface|string $discoveryUri): OpenidConfiguration
    {
        return OpenidConfiguration::fromDiscoveryUri($discoveryUri, $this->httpClient);
    }

    public function createRedirectUrl(
        string|UriInterface $discoveryUri,
        string $clientId,
        string|UriInterface $redirectUri,
        string $sessionId
    ): UriInterface {
        $configuration = $this->getConfiguration($discoveryUri);

        $authorizationEndpoint = $this->uriFactory->createUri($configuration->authorization_endpoint);

        $data = json_encode([
            // Discovery uri since it is our key for getting config
            "discoveryUri" => (string)$discoveryUri,
            "clientId" => $clientId,
            "redirectUri" => (string)$redirectUri
        ], JSON_THROW_ON_ERROR);

        $params = new OpenIdAuthenticationRequest(
            client_id: $clientId,
            redirect_uri: $redirectUri,
            state: hash_hmac('sha3-512', $data, $sessionId) . "$data"
        );


        parse_str($authorizationEndpoint->getQuery(), $queryParams);
        // Apply parameters to endpoint.
        $queryParams = [...$queryParams, ...$params->toQueryParamArray()];
        return $authorizationEndpoint->withQuery(http_build_query($queryParams));
    }


    public function handleStateCode(string $state, string $code, string $sessionId, HttpClient $authenticatedClient): IdToken
    {
        $response = OpenIdAuthenticationResponse::fromStateCode(
            $state,
            $code,
            $sessionId
        );
        return $this->handleResponse($response, $authenticatedClient);
    }

    private function handleResponse(
        OpenIdAuthenticationResponse $response,
        HttpClient $authenticatedClient
    ): IdToken {
        $configuration = $this->getConfiguration($response->discoveryUri);


        $body = [
            'code' => $response->code,
            'grant_type' => 'authorization_code',
            'client_id' => $response->clientId,
            'redirect_uri' => $response->redirectUri
        ];

        $request = $this->requestFactory->createRequest('POST', $configuration->token_endpoint)
            ->withBody($this->streamFactory->createStream(http_build_query($body)));


        $tokenResponse = $authenticatedClient->sendRequest($request);
        if ($tokenResponse->getStatusCode() !== 200) {
            throw new \RuntimeException("Failed to retrieve token");
        }

        $data = json_decode($tokenResponse->getBody()->getContents(), true, JSON_THROW_ON_ERROR);

        if (!is_array($data) || !isset($data['id_token']) || !is_string($data['id_token'])) {
            throw new \Exception('No id_token was found in the response, this violates the spec');
        }

        // Validate the token:  https://openid.net/specs/openid-connect-core-1_0.html#rfc.section.3.1.3.7
        return IdToken::fromClaims(JWT::decode($data['id_token'], $configuration->keySet));
    }
    public function handleRequest(
        ServerRequestInterface $request,
        string $sessionId,
        HttpClient $authenticatedClient
    ): IdToken {
        /**
         * This will validate the state.
         * All data on this response object except the `code` can be trusted since it was cryptographically secured
         */
        $response = OpenIdAuthenticationResponse::fromQueryParams(
            $request->getQueryParams(),
            $this->uriFactory,
            $sessionId
        );

        return $this->handleResponse($response, $authenticatedClient);
    }
}
