<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model\Query;

use IntegerNet\SansecWatch\Model\Config;
use IntegerNet\SansecWatch\Model\DTO\Policy;
use Magento\Framework\App\ResourceConnection;

class GetAllPolicies
{
    public function __construct(
        private readonly ResourceConnection $resourceConnection,
    ) {
    }

    /**
     * @return list<Policy>
     */
    public function execute(): array
    {
        $connection = $this->resourceConnection->getConnection('read');
        $query = $connection->select()
            ->from(Config::POLICY_TABLE);
        $policies = $connection->fetchAll($query);

        return array_map(Policy::fromArray(...), $policies);
    }
}
