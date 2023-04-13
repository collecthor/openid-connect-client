<?php

declare(strict_types=1);

namespace Collecthor\OpenidConnectClient;

class IdToken implements ClaimSetInterface
{
    /**
     * @param string $aud
     * @param string $iss
     * @param int $exp
     * @param string $sub
     * @param int $iat
     * @param array<string, scalar|array<mixed>> $customClaims
     */
    public function __construct(
        private readonly string $aud,
        private readonly string $iss,
        private readonly int $exp,
        private readonly string $sub,
        public readonly int $iat,
        private readonly array $customClaims
    ) {
    }

    /**
     * @param string $name
     * @return array<mixed>|scalar
     */
    public function getCustomClaim(string $name): array|bool|string|int|null|float
    {
        return $this->customClaims[$name] ?? null;
    }

    public function getIssuer(): string
    {
        return $this->iss;
    }

    public function getAudience(): string
    {
        return $this->aud;
    }

    public function getExpiration(): int
    {
        return $this->exp;
    }

    public function getSubject(): string
    {
        return $this->sub;
    }

    public function getIssuedAt(): int
    {
        return $this->iat;
    }

    /**
     * @return self
     */
    public static function fromClaims(\stdClass $claims): self
    {
        return self::fromArray((array) $claims);
    }

    /**
     * @psalm-assert array{aud: string, iss: string, exp: int, sub: string, iat: int} $claims
     * @param array<mixed> $claims
     * @return self
     */
    public static function fromArray(array $claims): self
    {
        if (!isset($claims['aud'], $claims['iss'], $claims['exp'], $claims['sub'], $claims['iat'])
            || !is_string($claims['aud'])
            || !is_string($claims['iss'])
            || !is_string($claims['sub'])
            || !is_int($claims['exp'])
            || !is_int($claims['iat'])

        ) {
            throw new \InvalidArgumentException('Incorrect claims array');
        }

        $customClaims = [];
        /**
         * @var mixed $value
         */
        foreach ($claims as $claim => $value) {
            if (in_array($claim, ['aud', 'iss', 'exp', 'sub', 'iat'])) {
                continue;
            }

            if (is_string($claim) &&
                (is_array($value) || is_scalar($value))
            ) {
                $customClaims[$claim] = $value;
            }
        }
        return new self(
            aud: $claims['aud'],
            iss: $claims['iss'],
            exp: $claims['exp'],
            sub: $claims['sub'],
            iat: $claims['iat'],
            customClaims: $customClaims
        );
    }
}
