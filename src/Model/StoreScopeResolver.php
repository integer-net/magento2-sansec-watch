<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

use function __;
use function is_int;
use function is_string;

class StoreScopeResolver
{
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
    ) {}

    public function resolveStoreId(mixed $storeIdentifier): ?int
    {
        if ($storeIdentifier === null || $storeIdentifier === '') {
            return null;
        }

        if (!is_string($storeIdentifier) && !is_int($storeIdentifier)) {
            throw new LocalizedException(__('Invalid store identifier.'));
        }

        return (int) $this->storeManager->getStore($storeIdentifier)->getId();
    }
}
