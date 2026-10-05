<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Service;

use IntegerNet\SansecWatch\Mapper\SansecWatchFlagMapper;
use IntegerNet\SansecWatch\Model\Command\UpdatePolicies as UpdatePoliciesCommand;
use IntegerNet\SansecWatch\Model\DTO\Policy;
use IntegerNet\SansecWatch\Model\DTO\SansecWatchFlag;
use IntegerNet\SansecWatch\Model\Event\FetchedPolicies;
use IntegerNet\SansecWatch\Model\Exception\CouldNotUpdatePoliciesException;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\FlagManager;
use Symfony\Component\Clock\ClockInterface;

use function is_array;
use function hash;
use function serialize;
use function sprintf;
use function usort;

class PolicyUpdater
{
    public function __construct(
        private readonly SansecWatchFlagMapper $flagDataMapper,
        private readonly FlagManager $flagManager,
        private readonly UpdatePoliciesCommand $updatePoliciesCommand,
        private readonly ClockInterface $clock,
        private readonly UpdateFpc $updateFpc,
        private readonly ManagerInterface $eventManager,
    ) {}

    /**
     * @param list<Policy> $policies
     *
     * @throws CouldNotUpdatePoliciesException
     */
    public function updatePolicies(
        array $policies,
        bool $force = false,
        ?int $storeId = null,
    ): void {
        $fetchedPolicies = new FetchedPolicies($policies);
        $this->eventManager->dispatch('integernet_sansec_watch_update_policies_before', [
            'fetched_policies' => $fetchedPolicies,
            'store_id'         => $storeId,
        ]);
        $policies = $fetchedPolicies->getPolicies();

        $newPoliciesHash  = $this->calculateHash($policies);
        $existingFlagData = $this->getPoliciesFlagData($storeId);

        if ($newPoliciesHash === $existingFlagData?->hash && $force === false) {
            $this->eventManager->dispatch('integernet_sansec_watch_update_policies_skipped', [
                'fetched_policies' => $fetchedPolicies,
                'policies_hash'    => $existingFlagData->hash,
                'store_id'         => $storeId,
            ]);

            $this->updateLastCheckedAt($existingFlagData, $storeId);
            return;
        }

        $this->updatePoliciesCommand->execute($policies, $storeId);
        $this->saveNewFlagData($newPoliciesHash, $storeId);
        $this->updateFpc->execute();

        $this->eventManager->dispatch('integernet_sansec_watch_update_policies_after', [
            'fetched_policies'  => $fetchedPolicies,
            'new_policies_hash' => $newPoliciesHash,
            'store_id'          => $storeId,
        ]);
    }

    private function saveNewFlagData(string $hash, ?int $storeId): void
    {
        $newFlagData = new SansecWatchFlag(
            hash         : $hash,
            lastCheckedAt: $this->clock->now(),
            lastUpdatedAt: $this->clock->now(),
        );

        $this->updateFlagData($newFlagData, $storeId);
    }

    private function updateLastCheckedAt(SansecWatchFlag $sansecWatchFlag, ?int $storeId): void
    {
        $newFlagData = new SansecWatchFlag(
            hash         : $sansecWatchFlag->hash,
            lastCheckedAt: $this->clock->now(),
            lastUpdatedAt: $sansecWatchFlag->lastUpdatedAt,
        );

        $this->updateFlagData($newFlagData, $storeId);
    }

    private function updateFlagData(SansecWatchFlag $flagData, ?int $storeId): void
    {
        $this->flagManager->saveFlag(
            SansecWatchFlag::getStoreCode($storeId),
            $flagData->jsonSerialize(),
        );
    }

    private function getPoliciesFlagData(?int $storeId): ?SansecWatchFlag
    {
        /** @var null|array{hash: string, lastCheckedAt: string, lastUpdatedAt: string} $flagData */
        $flagData = $this->flagManager->getFlagData(SansecWatchFlag::getStoreCode($storeId));

        if (!is_array($flagData)) {
            return null;
        }

        return $this->flagDataMapper->map($flagData);
    }

    /**
     * @param list<Policy> $policies
     */
    private function calculateHash(array $policies): string
    {
        usort($policies, static function (Policy $a, Policy $b) {
            return ($a->directive . $a->host) <=> ($b->directive . $b->host);
        });

        return hash('sha256', serialize($policies));
    }
}
