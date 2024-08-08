<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Cron;

use IntegerNet\SansecWatch\Model\Config;
use IntegerNet\SansecWatch\Model\SansecWatchClientFactory;
use IntegerNet\SansecWatch\Service\PolicyUpdater;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class UpdatePolicies
{
    public function __construct(
        private readonly Config $config,
        private readonly SansecWatchClientFactory $sansecWatchClientFactory,
        private readonly PolicyUpdater $policyUpdater,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function execute(): void
    {
        if (!$this->config->isEnabled()) {
            $this->logger->info('Update is disabled');
            return;
        }

        try {
            $uuid = $this->config->getId();

            $policies = $this->sansecWatchClientFactory
                ->create()
                ->fetchPolicies($uuid);

            $this->policyUpdater->updatePolicies($policies);
        } catch (LocalizedException $localizedException) {
            $this->logger->error('Could not update policies: ' . $localizedException->getMessage());
        }
    }
}
