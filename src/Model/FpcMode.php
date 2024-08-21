<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model;

use Magento\Framework\Phrase;

enum FpcMode: string
{
    case NONE = 'none';
    case INVALIDATE = 'invalidate';
    case CLEAR = 'clear';

    public function label(): Phrase
    {
        return match ($this) {
            FpcMode::NONE => __('Do Nothing'),
            FpcMode::INVALIDATE => __('Invalidate FPC'),
            FpcMode::CLEAR => __('Clear FPC'),
        };
    }
}
