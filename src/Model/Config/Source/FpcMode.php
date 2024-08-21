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
                'value' => FpcModeEnum::NONE->value,
                'label' => FpcModeEnum::NONE->label(),
            ],
            [
                'value' => FpcModeEnum::INVALIDATE->value,
                'label' => FpcModeEnum::INVALIDATE->label(),
            ],
            [
                'value' => FpcModeEnum::CLEAR->value,
                'label' => FpcModeEnum::CLEAR->label(),
            ],
        ];
    }
}
