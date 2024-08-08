<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model\Exception;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Throwable;

class CouldNotUpdatePoliciesException extends LocalizedException
{
    public function __construct(
        Phrase $phrase,
        public readonly ?Throwable $previous = null
    ) {
        parent::__construct($phrase);
    }

    public static function withMessage(Phrase $phrase, ?Throwable $previous = null): self
    {
        return new self($phrase, $previous);
    }
}
