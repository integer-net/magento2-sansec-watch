<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model\DTO;

use DateTimeImmutable;
use JsonSerializable;

class SansecWatchFlag implements JsonSerializable
{
    public const CODE = 'integernet_sansecwatch';

    public function __construct(
        public readonly string $hash,
        public readonly DateTimeImmutable $lastCheckedAt,
        public readonly DateTimeImmutable $lastUpdatedAt,
    ) {
    }

    /**
     * @phpstan-return array{
     *     hash: string,
     *     last_checked_at: string,
     *     last_updated_at: string
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'hash' => $this->hash,
            'last_checked_at' => $this->lastCheckedAt->format(DATE_ATOM),
            'last_updated_at' => $this->lastUpdatedAt->format(DATE_ATOM),
        ];
    }
}
