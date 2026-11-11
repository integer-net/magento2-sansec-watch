<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model\Config\Source;

use IntegerNet\SansecWatch\Model\DirectiveSetting;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Phrase;

class YesNoInherited implements OptionSourceInterface
{
    /**
     * @return list<array{value: string, label: Phrase}>
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => DirectiveSetting::Yes->value,
                'label' => DirectiveSetting::Yes->label(),
            ],
            [
                'value' => DirectiveSetting::No->value,
                'label' => DirectiveSetting::No->label(),
            ],
            [
                'value' => DirectiveSetting::Inherited->value,
                'label' => DirectiveSetting::Inherited->label(),
            ],
        ];
    }
}
