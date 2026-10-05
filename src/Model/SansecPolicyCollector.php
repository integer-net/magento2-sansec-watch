<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model;

use IntegerNet\SansecWatch\Model\Query\GetAllPolicies;
use Magento\Csp\Api\PolicyCollectorInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

use function array_map;
use function array_merge;

class SansecPolicyCollector implements PolicyCollectorInterface
{
    public function __construct(
        private readonly GetAllPolicies $getAllPolicies,
        private readonly FetchPolicyFactory $fetchPolicyFactory,
        private readonly State $state,
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager,
    ) {}

    /**
     * @throws LocalizedException
     */
    public function collect(array $defaultPolicies = []): array
    {
        if ($this->state->getAreaCode() !== Area::AREA_FRONTEND) {
            return $defaultPolicies;
        }

        $storeId = (int) $this->storeManager
            ->getStore()
            ->getId();

        if (!$this->config->isEnabled($storeId)) {
            return $defaultPolicies;
        }

        $sansecWatchPolicies = array_map(
            $this->fetchPolicyFactory->fromPolicyDto(...),
            $this->getAllPolicies->execute($storeId),
        );

        return array_merge($defaultPolicies, $sansecWatchPolicies);
    }
}
