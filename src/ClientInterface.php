<?php
declare(strict_types=1);

namespace Collecthor\OpenidConnectClient;

use Psr\Http\Client\ClientInterface as HttpClient;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * @psalm-api
 */
interface ClientInterface
{

    public function createRedirectUrl(UriInterface $discoveryUri,
        string $clientId,
        UriInterface $redirectUri,
        string $sessionId
    ): UriInterface;

    /**
     * @param ServerRequestInterface $request
     * @param HttpClient $authenticatedClient An HTTP client that will insert the correct credentials when sending a request.
     * @return ClaimSetInterface
     * @throws \Exception For any non-successful flow exceptions should be thrown
     */
    public function handleRequest(ServerRequestInterface $request,
        string $sessionId,
        HttpClient $authenticatedClient): ClaimSetInterface;

}
