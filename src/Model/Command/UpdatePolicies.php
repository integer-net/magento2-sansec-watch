<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model\Command;

use Exception;
use IntegerNet\SansecWatch\Model\Config;
use IntegerNet\SansecWatch\Model\DTO\Policy;
use Magento\Framework\App\ResourceConnection;

class UpdatePolicies
{
    private const TABLE = 'integernet_sansecwatch_policies';

    public function __construct(
        private readonly ResourceConnection $resourceConnection,
    ) {
    }

    /**
     * @param list<Policy> $policies
     */
    public function execute(array $policies): void
    {
        $connection = $this->resourceConnection->getConnection('write');

        try {
            $connection->beginTransaction();
            $connection->delete(self::TABLE);
            $connection->insertMultiple(Config::POLICY_TABLE, array_map(fn (Policy $p): array => $p->toArray(), $policies));
            $connection->commit();
        } catch (Exception) {
            $connection->rollBack();
        }
    }
}
