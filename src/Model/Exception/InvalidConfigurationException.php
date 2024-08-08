<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model\Exception;

use Magento\Framework\Exception\LocalizedException;

class InvalidConfigurationException extends LocalizedException
{
    public static function fromInvalidUuid(string $uuid): self
    {
        return new self(__('Invalid ID provided: %1, expected UUID format', $uuid));
    }
}
