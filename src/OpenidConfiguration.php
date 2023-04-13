<?php
declare(strict_types=1);

namespace Collecthor\OpenidConnectClient;

use Firebase\JWT\JWK;
use Firebase\JWT\Key;
use Psr\Http\Client\ClientInterface as ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * See https://openid.net/specs/openid-connect-discovery-1_0.html#ProviderMetadata
 *
 * This class does not specify property naming conventions in favor of using the exact same names as in the specification.
 *
 * Any property without default value is required
 */
class OpenidConfiguration
{

    /**
     * @param array<string, Key> $keySet
     */
    public function __construct(
        public readonly string $authorization_endpoint,
        public readonly string $token_endpoint,
        public readonly array $keySet
    )
    {
    }



    public static function fromDiscoveryUri(
        UriInterface|string $uri,
        JsonHttpClient $client,
    ): self
    {
        $discoveredConfig = $client->getJsonArray($uri);

        if(!isset($discoveredConfig['jwks_uri'], $discoveredConfig['authorization_endpoint'], $discoveredConfig['token_endpoint'])
            || !is_string($discoveredConfig['jwks_uri'])
            || !is_string($discoveredConfig['authorization_endpoint'])
            || !is_string($discoveredConfig['token_endpoint'])
        ) {
            throw new \Exception('Discovery document does not contain all required keys');
        }

        $jwks = $client->getJsonArray($discoveredConfig['jwks_uri']);

        /**
         * We pass in a default algorithm, although we really shouldn't be required to https://github.com/firebase/php-jwt/issues/498
         */
        $parsedJwks = JWK::parseKeySet($jwks, 'RS256');


        return new self(authorization_endpoint: $discoveredConfig['authorization_endpoint'],
            token_endpoint: $discoveredConfig['token_endpoint'], keySet: $parsedJwks);

        ;



    }

}
