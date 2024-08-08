<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Service;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use DateTimeImmutable;
use IntegerNet\SansecWatch\Model\Command\UpdatePolicies;
use IntegerNet\SansecWatch\Model\DTO\Policy;
use IntegerNet\SansecWatch\Model\DTO\SansecWatchFlag;
use IntegerNet\SansecWatch\Model\Exception\CouldNotUpdatePoliciesException;
use Magento\Framework\FlagManager;

class PolicyUpdater
{
    public function __construct(
        private readonly FlagManager $flagManager,
        private readonly UpdatePolicies $updatePolicies,
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

        $this->updatePolicies->execute($policies);
        $this->saveNewFlagData($newPoliciesHash);
    }

    private function saveNewFlagData(string $hash): void
    {
        $newFlagData = new SansecWatchFlag(
            hash         : $hash,
            lastCheckedAt: new DateTimeImmutable(),
            lastUpdatedAt: new DateTimeImmutable(),
        );

        $this->updateFlagData($newFlagData);
    }

    private function updateLastCheckedAt(SansecWatchFlag $sansecWatchFlag): void
    {
        $newFlagData = new SansecWatchFlag(
            hash         : $sansecWatchFlag->hash,
            lastCheckedAt: new DateTimeImmutable(),
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
        $flagData = $this->flagManager->getFlagData(SansecWatchFlag::CODE);

        if (!is_array($flagData)) {
            return null;
        }

        try {
            return (new MapperBuilder())
                ->allowSuperfluousKeys()
                ->supportDateFormats(DATE_ATOM)
                ->mapper()
                ->map(
                    SansecWatchFlag::class,
                    Source::array($flagData)
                        ->camelCaseKeys()
                );
        } catch (MappingError) {
            return null;
        }
    }

    /**
     * @param list<Policy> $policies
     */
    private function calculateHash(array $policies): string
    {
        return hash('sha256', serialize($policies));
    }
}
