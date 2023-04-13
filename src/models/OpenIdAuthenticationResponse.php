<?php
declare(strict_types=1);

namespace Collecthor\OpenidConnectClient\models;

use Collecthor\OpenidConnectClient\OAuth2AuthenticationException;
use Psr\Http\Message\UriFactoryInterface;

class OpenIdAuthenticationResponse
{
    public function __construct(
        public readonly string $code,
        public readonly string $discoveryUri,
        public readonly string $clientId,
        public readonly string $redirectUri
    ) {

    }


    public static function fromStateCode(
        string $state,
        string $code,
        string $hashKey
    ): self {
        if (!preg_match('/^[0-9A-Fa-f]{128}\{.*\}$/', $state)) {
            throw new \InvalidArgumentException('Invalid state format');
        }
        $stateHash = substr($state, 0, 128);

        $stateData = substr($state, 128);

        $expectedHash = hash_hmac('sha3-512', $stateData, $hashKey);
        if (!hash_equals($expectedHash, $stateHash)) {
            throw new  \InvalidArgumentException('Invalid state HMAC');
        }

        /**
         * @var array{discoveryUri: string, clientId: string, redirectUri: string}
         */
        $decodedData = json_decode($stateData, true, JSON_THROW_ON_ERROR);




        return new self(
            code: $code,
            discoveryUri: $decodedData['discoveryUri'],
            clientId: $decodedData['clientId'],
            redirectUri: $decodedData['redirectUri']
        );
    }

    /**
     * @psalm-assert array{code?:string, state?:string, error?:string, error_uri?:string, error_description?:string} $queryParams
     * @phpstan-assert array{code?:string, state?:string, error?:string, error_uri?:string, error_description?:string} $queryParams
     * @param UriFactoryInterface $uriFactory
     * @param array<mixed> $queryParams
     * @param string $hashKey
     * @return self
     * @throws OAuth2AuthenticationException
     */
    public static function fromQueryParams(
        array $queryParams,
        UriFactoryInterface $uriFactory,
        string $hashKey
    ): self
    {

        if (isset($queryParams['code'], $queryParams['state'])
            && is_string($queryParams['code'])
            && is_string($queryParams['state'])
        ) {
            return self::fromStateCode($queryParams['state'], $queryParams['code'], $hashKey);
        }
        if (isset($queryParams['error']) && is_string($queryParams['error'])) {
            throw new OAuth2AuthenticationException(OAuth2AuthenticationError::from($queryParams['error']),
                isset($queryParams['error_uri']) && is_string($queryParams['error_uri']) ? $uriFactory->createUri($queryParams['error_uri']) : null,
                isset($queryParams['error_description']) && is_string($queryParams['error_description']) ? $queryParams['error_description'] : null
            );
        }

        throw new \InvalidArgumentException('params are out of spec');
    }

}
