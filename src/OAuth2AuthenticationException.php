<?php

declare(strict_types=1);

namespace Collecthor\OpenidConnectClient;

use Collecthor\OpenidConnectClient\models\OAuth2AuthenticationError;
use Psr\Http\Message\UriInterface;

/**
 * @psalm-api
 */
class OAuth2AuthenticationException extends \Exception
{
    public function __construct(
        public readonly OAuth2AuthenticationError $error,
        public readonly null|UriInterface $errorUri = null,
        string $message = null
    ) {
        parent::__construct($message ?? $error->description());
    }
}
