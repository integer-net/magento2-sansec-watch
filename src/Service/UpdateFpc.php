<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Service;

use IntegerNet\SansecWatch\Model\Config;
use IntegerNet\SansecWatch\Model\FpcMode;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\PageCache\Model\Cache\Type;
use Magento\PageCache\Model\Config as PageCacheConfig;

class UpdateFpc
{
    public function __construct(
        private readonly Config $config,
        private readonly PageCacheConfig $pageCacheConfig,
        private readonly TypeListInterface $cacheList,
    ) {
    }

    public function execute(): void
    {
        if (!$this->pageCacheConfig->isEnabled()) {
            return;
        }

        $mode = $this->config->getFpcMode();
        if ($mode === FpcMode::NONE) {
            return;
        }

        /** @noinspection PhpUncoveredEnumCasesInspection */
        match ($mode) {
            FpcMode::CLEAR => $this->cacheList->cleanType(Type::TYPE_IDENTIFIER),
            FpcMode::INVALIDATE => $this->cacheList->invalidate(Type::TYPE_IDENTIFIER),
        };
    }
}
