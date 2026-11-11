<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model;

use Magento\Framework\Phrase;

use function __;

enum DirectiveSetting: string
{
    case Yes       = 'yes';
    case No        = 'no';
    case Inherited = 'inherited';

    public function label(): Phrase
    {
        return match ($this) {
            self::Yes       => __('Yes'),
            self::No        => __('No'),
            self::Inherited => __('Inherited'),
        };
    }
}
