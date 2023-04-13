<?php
declare(strict_types=1);

namespace Collecthor\OpenidConnectClient;

/**
 * @psalm-api
 */
interface ClaimSetInterface
{

    /**
     * @param string $name
     * @return array<mixed>|scalar
     */
    public function getCustomClaim(string $name): array|string|int|null|bool|float;

    public function getIssuer(): string;

    public function getAudience(): string;

    public function getSubject(): string;

    public function getExpiration(): int;

    public function getIssuedAt(): int;


}
