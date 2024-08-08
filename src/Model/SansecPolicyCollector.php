<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model;

use IntegerNet\SansecWatch\Model\Query\GetAllPolicies;
use Magento\Csp\Api\PolicyCollectorInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;

class SansecPolicyCollector implements PolicyCollectorInterface
{
    public function __construct(
        private readonly GetAllPolicies $getAllPolicies,
        private readonly FetchPolicyFactory $fetchPolicyFactory,
        private readonly State $state,
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function collect(array $defaultPolicies = []): array
    {
        if ($this->state->getAreaCode() === Area::AREA_ADMINHTML) {
            return $defaultPolicies;
        }

        $sansecWatchPolicies = array_map(
            $this->fetchPolicyFactory->fromPolicyDto(...),
            $this->getAllPolicies->execute()
        );

        return array_merge($defaultPolicies, $sansecWatchPolicies);
    }
}
