<?php
/** @noinspection DuplicatedCode */

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Test\Service;

use DateInterval;
use DateTimeImmutable;
use IntegerNet\SansecWatch\Mapper\SansecWatchFlagMapper;
use IntegerNet\SansecWatch\Model\Command\UpdatePolicies;
use IntegerNet\SansecWatch\Model\DTO\Policy;
use IntegerNet\SansecWatch\Model\DTO\SansecWatchFlag;
use IntegerNet\SansecWatch\Service\PolicyUpdater;
use IntegerNet\SansecWatch\Service\UpdateFpc;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\FlagManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\ClockInterface;

#[CoversClass(PolicyUpdater::class)]
class PolicyUpdaterTest extends TestCase
{
    private SansecWatchFlagMapper&Stub $flagDataMapper;
    private FlagManager&MockObject $flagManager;
    private UpdatePolicies&MockObject $updatePolicies;
    private ClockInterface&Stub $clock;
    private UpdateFpc&MockObject $updateFpc;

    private PolicyUpdater $policyUpdater;

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    protected function setUp(): void
    {
        $this->flagDataMapper = self::createStub(SansecWatchFlagMapper::class);
        $this->clock          = self::createStub(ClockInterface::class);
        $this->flagManager    = self::createMock(FlagManager::class);
        $this->updatePolicies = self::createMock(UpdatePolicies::class);
        $this->updateFpc      = self::createMock(UpdateFpc::class);
        $eventManager         = self::createStub(ManagerInterface::class);

        $this->policyUpdater = new PolicyUpdater(
            $this->flagDataMapper,
            $this->flagManager,
            $this->updatePolicies,
            $this->clock,
            $this->updateFpc,
            $eventManager,
        );
    }

    #[Test]
    public function pageCacheIsClearedIfPoliciesAreUpdated(): void
    {
        $this->flagDataIsReturned(
            new SansecWatchFlag(
                hash('sha256', serialize([])),
                new DateTimeImmutable(),
                new DateTimeImmutable(),
            )
        );

        $this->updateFpc
            ->expects(self::once())
            ->method('execute');

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->policyUpdater->updatePolicies([new Policy('script-src', '*.integer-net.de')]);
    }

    #[Test]
    public function pageCacheIsIgnoredIfPoliciesAreNotUpdated(): void
    {
        $policies = [new Policy('script-src', '*.integer-net.de')];

        $this->flagDataIsReturned(
            new SansecWatchFlag(
                hash('sha256', serialize($policies)),
                new DateTimeImmutable(),
                new DateTimeImmutable(),
            )
        );

        $this->updateFpc
            ->expects(self::never())
            ->method('execute');

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->policyUpdater->updatePolicies($policies);
    }

    #[Test]
    public function policiesAreNotUpdatedIfHashDoesMatch(): void
    {
        $policies = [new Policy('script-src', '*.integer-net.de')];

        $this->flagDataIsReturned(
            new SansecWatchFlag(
                hash('sha256', serialize($policies)),
                new DateTimeImmutable(),
                new DateTimeImmutable(),
            )
        );

        $this->updatePolicies
            ->expects(self::never())
            ->method('execute');

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->policyUpdater->updatePolicies($policies);
    }

    #[Test]
    public function lastCheckedUpIsUpdatedWhenHashMatches(): void
    {
        $policies  = [new Policy('script-src', '*.integer-net.de')];
        $hash      = hash('sha256', serialize($policies));
        $now       = new DateTimeImmutable();
        $yesterday = $now->sub(DateInterval::createFromDateString('1 day'));

        $this->clock
            ->method('now')
            ->willReturn($now);

        $this->flagDataIsReturned(new SansecWatchFlag($hash, $now, $yesterday));

        $this->flagManager
            ->expects(self::once())
            ->method('saveFlag')
            ->with(
                SansecWatchFlag::CODE,
                [
                    'hash'            => $hash,
                    'last_checked_at' => $now->format(DATE_ATOM),
                    'last_updated_at' => $yesterday->format(DATE_ATOM),
                ]
            );

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->policyUpdater->updatePolicies($policies);
    }

    #[Test]
    public function flagDataIsAlwaysUpdatedIfForceIsTrue(): void
    {
        $policies  = [new Policy('script-src', '*.integer-net.de')];
        $hash      = hash('sha256', serialize($policies));
        $now       = new DateTimeImmutable();
        $yesterday = $now->sub(DateInterval::createFromDateString('1 day'));

        $this->clock
            ->method('now')
            ->willReturn($now);

        $this->flagDataIsReturned(new SansecWatchFlag($hash, $now, $yesterday));

        $this->updatePolicies
            ->expects(self::once())
            ->method('execute')
            ->with($policies);

        $this->flagManager
            ->expects(self::once())
            ->method('saveFlag')
            ->with(
                SansecWatchFlag::CODE,
                [
                    'hash'            => $hash,
                    'last_checked_at' => $now->format(DATE_ATOM),
                    'last_updated_at' => $now->format(DATE_ATOM),
                ]
            );

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->policyUpdater->updatePolicies($policies, true);
    }

    private function flagDataIsReturned(?SansecWatchFlag $flag): void
    {
        $this->flagManager
            ->method('getFlagData')
            ->willReturn([]);

        $this->flagDataMapper
            ->method('map')
            ->willReturn($flag);
    }
}
