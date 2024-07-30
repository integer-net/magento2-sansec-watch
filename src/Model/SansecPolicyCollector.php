<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model;

use IntegerNet\SansecWatch\Model\Query\GetAllPolicies;
use Magento\Csp\Api\PolicyCollectorInterface;

class SansecPolicyCollector implements PolicyCollectorInterface
{
    public function __construct(
        private readonly GetAllPolicies $getAllPolicies,
        private readonly FetchPolicyFactory $fetchPolicyFactory,
    ) {
    }

    public function collect(array $defaultPolicies = []): array
    {
        $sansecWatchPolicies = array_map(
            $this->fetchPolicyFactory->fromPolicyDto(...),
            $this->getAllPolicies->execute()
        );

        return array_merge($defaultPolicies, $sansecWatchPolicies);
    }
}
