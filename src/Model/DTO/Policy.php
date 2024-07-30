<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model\DTO;

class Policy
{
    public function __construct(
        public readonly string $directive,
        public readonly string $host,
    ) {
    }

    /**
     * @phpstan-param array{directive: string, host: string} $array
     */
    public static function fromArray(array $array): self
    {
        return new self(
            directive: $array['directive'],
            host     : $array['host'],
        );
    }

    /**
     * @phpstan-return array{directive: string, host: string}
     */
    public function toArray(): array
    {
        return [
            'directive' => $this->directive,
            'host'      => $this->host,
        ];
    }
}
