<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model\Config\Source;

use IntegerNet\SansecWatch\Model\FpcMode as FpcModeEnum;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Phrase;

class FpcMode implements OptionSourceInterface
{
    /**
     * @return list<array{value: string, label: Phrase}>
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => FpcModeEnum::None->value,
                'label' => FpcModeEnum::None->label(),
            ],
            [
                'value' => FpcModeEnum::Invalidate->value,
                'label' => FpcModeEnum::Invalidate->label(),
            ],
            [
                'value' => FpcModeEnum::Clear->value,
                'label' => FpcModeEnum::Clear->label(),
            ],
        ];
    }
}
