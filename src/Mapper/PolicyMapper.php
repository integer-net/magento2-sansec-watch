<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Mapper;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Exception\InvalidSource;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use IntegerNet\SansecWatch\Model\DTO\Policy;

class PolicyMapper
{
    /**
     * @phpstan-return list<Policy>
     *
     * @throws MappingError
     * @throws InvalidSource
     */
    public function map(string $json): array
    {
        return (new MapperBuilder())
            ->mapper()
            ->map(
                sprintf('list<%s>', Policy::class),
                Source::json($json)
            );
    }
}
