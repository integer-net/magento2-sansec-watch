<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Mapper;

use CuyZ\Valinor\Mapper\Configurator\MapKeysToCamelCase;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use IntegerNet\SansecWatch\Model\DTO\SansecWatchFlag;

class SansecWatchFlagMapper
{
    /**
     * @phpstan-param array{
     *     hash: string,
     *     lastCheckedAt: string,
     *     lastUpdatedAt: string
     * } $flagData
     */
    public function map(array $flagData): ?SansecWatchFlag
    {
        try {
            return (new MapperBuilder())
                ->configureWith(
                    new MapKeysToCamelCase(),
                )
                ->allowSuperfluousKeys()
                ->supportDateFormats(DATE_ATOM)
                ->mapper()
                ->map(SansecWatchFlag::class, Source::array($flagData));
        } catch (MappingError) {
            return null;
        }
    }
}
