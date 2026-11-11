<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model\Query;

use Exception;
use IntegerNet\SansecWatch\Model\Config;
use IntegerNet\SansecWatch\Model\DTO\Policy;
use Magento\Framework\App\ResourceConnection;

use function array_map;
use function array_values;
use function count;

class GetAllPolicies
{
    /**
     * @var array<string, list<Policy>>
     */
    private array $policies = [];

    public function __construct(
        private readonly ResourceConnection $resourceConnection,
    ) {}

    /**
     * @return list<Policy>
     */
    public function execute(?int $storeId = null): array
    {
        $cacheKey = $storeId === null
            ? 'default'
            : (string) $storeId;

        if (isset($this->policies[$cacheKey])) {
            return $this->policies[$cacheKey];
        }

        $policies = $this->fetchPolicies($storeId);
        if ($policies === null && $storeId !== null) {
            $policies = $this->fetchPolicies(null);
        }

        return $this->policies[$cacheKey] = $policies ?? [];
    }

    /**
     * @return list<Policy>
     */
    private function fetchPolicies(?int $storeId): ?array
    {
        $connection = $this->resourceConnection->getConnection('read');
        $tableName  = $this->resourceConnection->getTableName(Config::POLICY_TABLE);

        try {
            $query = $connection->select()
                ->from($tableName);

            $storeId !== null
                ? $query->where('store_id = ?', $storeId)
                : $query->where('store_id IS NULL');

            $policies = $connection->fetchAll($query);
        } catch (Exception) {
            return null;
        }

        $policies = array_values(array_map(Policy::fromArray(...), $policies));

        return count($policies) > 0
            ? $policies
            : null;
    }
}
