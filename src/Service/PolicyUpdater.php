<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Service;

use IntegerNet\SansecWatch\Mapper\SansecWatchFlagMapper;
use IntegerNet\SansecWatch\Model\Command\UpdatePolicies as UpdatePoliciesCommand;
use IntegerNet\SansecWatch\Model\DTO\Policy;
use IntegerNet\SansecWatch\Model\DTO\SansecWatchFlag;
use IntegerNet\SansecWatch\Model\Exception\CouldNotUpdatePoliciesException;
use Magento\Framework\FlagManager;
use Symfony\Component\Clock\ClockInterface;

class PolicyUpdater
{
    public function __construct(
        private readonly SansecWatchFlagMapper $flagDataMapper,
        private readonly FlagManager $flagManager,
        private readonly UpdatePoliciesCommand $updatePoliciesCommand,
        private readonly ClockInterface $clock,
        private readonly UpdateFpc $updateFpc,
    ) {
    }

    /**
     * @param list<Policy> $policies
     *
     * @throws CouldNotUpdatePoliciesException
*/
    public function updatePolicies(array $policies, bool $force = false): void
    {
        $newPoliciesHash  = $this->calculateHash($policies);
        $existingFlagData = $this->getPoliciesFlagData();

        if ($newPoliciesHash === $existingFlagData?->hash && $force === false) {
            $this->updateLastCheckedAt($existingFlagData);
            return;
        }

        $this->updatePoliciesCommand->execute($policies);
        $this->saveNewFlagData($newPoliciesHash);
        $this->updateFpc->execute();
    }

    private function saveNewFlagData(string $hash): void
    {
        $newFlagData = new SansecWatchFlag(
            hash         : $hash,
            lastCheckedAt: $this->clock->now(),
            lastUpdatedAt: $this->clock->now(),
        );

        $this->updateFlagData($newFlagData);
    }

    private function updateLastCheckedAt(SansecWatchFlag $sansecWatchFlag): void
    {
        $newFlagData = new SansecWatchFlag(
            hash         : $sansecWatchFlag->hash,
            lastCheckedAt: $this->clock->now(),
            lastUpdatedAt: $sansecWatchFlag->lastUpdatedAt,
        );

        $this->updateFlagData($newFlagData);
    }

    private function updateFlagData(SansecWatchFlag $flagData): void
    {
        $this->flagManager->saveFlag(SansecWatchFlag::CODE, $flagData->jsonSerialize());
    }

    private function getPoliciesFlagData(): ?SansecWatchFlag
    {
        /** @var null|array{hash: string, lastCheckedAt: string, lastUpdatedAt: string} $flagData */
        $flagData = $this->flagManager->getFlagData(SansecWatchFlag::CODE);

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
        return hash('sha256', serialize($policies));
    }
}
