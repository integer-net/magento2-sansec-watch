<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model\Command;

use Exception;
use IntegerNet\SansecWatch\Model\Config;
use IntegerNet\SansecWatch\Model\DTO\Policy;
use IntegerNet\SansecWatch\Model\Exception\CouldNotUpdatePoliciesException;
use Magento\Framework\App\ResourceConnection;

use function __;
use function array_map;

class UpdatePolicies
{
    public function __construct(
        private readonly ResourceConnection $resourceConnection,
    ) {}

    /**
     * @param list<Policy> $policies
     *
     * @throws CouldNotUpdatePoliciesException
     */
    public function execute(array $policies, ?int $storeId = null): void
    {
        $connection = $this->resourceConnection->getConnection('write');
        $tableName  = $this->resourceConnection->getTableName(Config::POLICY_TABLE);

        try {
            $connection->beginTransaction();

            $deleteCondition = $storeId !== null
                ? $connection->quoteInto('store_id = ?', $storeId)
                : 'store_id IS NULL';

            $connection->delete($tableName, $deleteCondition);

            if ($policies !== []) {
                $policiesData = array_map(static fn(Policy $p): array => $p->toArray(), $policies);
                $policiesData = array_map(static fn(array $p): array => $p + ['store_id' => $storeId], $policiesData);

                $connection->insertMultiple($tableName, $policiesData);
            }

            $connection->commit();
        } catch (Exception $exception) {
            $connection->rollBack();

            throw CouldNotUpdatePoliciesException::withMessage(
                $storeId !== null
                    ? __('Could not update policies for store %1: %2', $storeId, $exception->getMessage())
                    : __('Could not update policies: %1', $exception->getMessage()),
            );
        }
    }
}
