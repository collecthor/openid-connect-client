<?php

declare(strict_types=1);

namespace Collecthor\OpenidConnectClient\models;

use Psr\Http\Message\UriInterface;

class OpenIdAuthenticationRequest
{
    /**
     * @param list<string> $extraScopes
     */
    public function __construct(
        private readonly string $client_id,
        private readonly string|UriInterface $redirect_uri,
        private readonly string $state,
        private readonly array $extraScopes = ['email'],
    ) {
    }

    /**
     * @return array{response_type: string, client_id: string, redirect_uri: string, state: string, scope: string}
     */
    public function toQueryParamArray(): array
    {
        return [
            'response_type' => 'code',
            'client_id' => $this->client_id,
            'redirect_uri' => (string) $this->redirect_uri,
            'state' => $this->state,
            'scope' => implode(' ', ['openid', ...$this->extraScopes])


        ];
    }
}
