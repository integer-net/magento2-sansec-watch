<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model\Command;

use Exception;
use IntegerNet\SansecWatch\Model\Config;
use IntegerNet\SansecWatch\Model\DTO\Policy;
use IntegerNet\SansecWatch\Model\Exception\CouldNotUpdatePoliciesException;
use Magento\Framework\App\ResourceConnection;

class UpdatePolicies
{
    public function __construct(
        private readonly ResourceConnection $resourceConnection,
    ) {
    }

    /**
     * @param list<Policy> $policies
     *
     * @throws CouldNotUpdatePoliciesException
     */
    public function execute(array $policies): void
    {
        $connection = $this->resourceConnection->getConnection('write');
        $tableName = $connection->getTableName(Config::POLICY_TABLE);

        try {
            $connection->beginTransaction();
            $connection->delete($tableName);

            if ($policies !== []) {
                $connection->insertMultiple($tableName, array_map(fn (Policy $p): array => $p->toArray(), $policies));
            }

            $connection->commit();
        } catch (Exception $exception) {
            $connection->rollBack();

            throw CouldNotUpdatePoliciesException::withMessage(
                __('Could not update policies: %1', $exception->getMessage())
            );
        }
    }
}
