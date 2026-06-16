<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model;

use Magento\Framework\Phrase;

use function __;

enum FpcMode: string
{
    case None       = 'none';
    case Invalidate = 'invalidate';
    case Clear      = 'clear';

    public function label(): Phrase
    {
        return match ($this) {
            self::None       => __('Do Nothing'),
            self::Invalidate => __('Invalidate FPC'),
            self::Clear      => __('Clear FPC'),
        };
    }
}
