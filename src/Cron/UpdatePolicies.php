<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Cron;

use IntegerNet\SansecWatch\Model\Config;
use IntegerNet\SansecWatch\Model\SansecWatchClientFactory;
use IntegerNet\SansecWatch\Service\PolicyUpdater;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

use function sprintf;

class UpdatePolicies
{
    public function __construct(
        private readonly Config $config,
        private readonly SansecWatchClientFactory $sansecWatchClientFactory,
        private readonly PolicyUpdater $policyUpdater,
        private readonly LoggerInterface $logger,
        private readonly StoreManagerInterface $storeManager,
    ) {}

    public function execute(): void
    {
        if (!$this->config->isCronEnabled()) {
            return;
        }

        foreach ($this->storeManager->getStores() as $store) {
            if (!(bool) $store->getIsActive()) {
                continue;
            }

            $storeId = (int) $store->getId();
            if ($this->config->isEnabled($storeId)) {
                $this->updateStorePolicies($storeId);
            }
        }
    }

    private function updateStorePolicies(int $storeId): void
    {
        try {
            $uuid = $this->config->getId($storeId);

            $policies = $this->sansecWatchClientFactory
                ->create()
                ->fetchPolicies($uuid);

            $this->policyUpdater->updatePolicies($policies, storeId: $storeId);
        } catch (LocalizedException $localizedException) {
            $this->logger->error(
                sprintf(
                    'Could not update policies for store %d: %s',
                    $storeId,
                    $localizedException->getMessage(),
                ),
            );
        }
    }
}
