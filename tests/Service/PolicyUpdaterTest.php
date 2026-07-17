<?php
/** @noinspection DuplicatedCode */

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Test\Service;

use DateInterval;
use DateTimeImmutable;
use IntegerNet\SansecWatch\Mapper\SansecWatchFlagMapper;
use IntegerNet\SansecWatch\Model\Command\UpdatePolicies as UpdatePoliciesCommand;
use IntegerNet\SansecWatch\Model\DTO\Policy;
use IntegerNet\SansecWatch\Model\DTO\SansecWatchFlag;
use IntegerNet\SansecWatch\Service\PolicyUpdater;
use IntegerNet\SansecWatch\Service\UpdateFpc;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\FlagManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\ClockInterface;

#[CoversClass(PolicyUpdater::class)]
class PolicyUpdaterTest extends TestCase
{
    #[Test]
    public function pageCacheIsClearedIfPoliciesAreUpdated(): void
    {
        $flag = new SansecWatchFlag(
            hash('sha256', serialize([])),
            new DateTimeImmutable(),
            new DateTimeImmutable(),
        );

        $flagManagerStub = self::createStub(FlagManager::class);
        $flagManagerStub->method('getFlagData')->willReturn([]);

        $flagDataMapperStub = self::createStub(SansecWatchFlagMapper::class);
        $flagDataMapperStub->method('map')->willReturn($flag);

        $updateFpc = $this->createMock(UpdateFpc::class);
        $updateFpc->expects($this->once())->method('execute');

        /** @noinspection PhpUnhandledExceptionInspection */
        (new PolicyUpdater(
            flagDataMapper       : $flagDataMapperStub,
            flagManager          : $flagManagerStub,
            updatePoliciesCommand: self::createStub(UpdatePoliciesCommand::class),
            clock                : self::createStub(ClockInterface::class),
            updateFpc            : $updateFpc,
            eventManager         : self::createStub(ManagerInterface::class),
        ))->updatePolicies([new Policy('script-src', '*.integer-net.de')]);
    }

    #[Test]
    public function pageCacheIsIgnoredIfPoliciesAreNotUpdated(): void
    {
        $policies = [new Policy('script-src', '*.integer-net.de')];

        $flag = new SansecWatchFlag(
            hash('sha256', serialize($policies)),
            new DateTimeImmutable(),
            new DateTimeImmutable(),
        );

        $flagManagerStub = self::createStub(FlagManager::class);
        $flagManagerStub->method('getFlagData')->willReturn([]);

        $flagDataMapperStub = self::createStub(SansecWatchFlagMapper::class);
        $flagDataMapperStub->method('map')->willReturn($flag);

        $updateFpc = $this->createMock(UpdateFpc::class);
        $updateFpc->expects($this->never())->method('execute');

        /** @noinspection PhpUnhandledExceptionInspection */
        (new PolicyUpdater(
            flagDataMapper       : $flagDataMapperStub,
            flagManager          : $flagManagerStub,
            updatePoliciesCommand: self::createStub(UpdatePoliciesCommand::class),
            clock                : self::createStub(ClockInterface::class),
            updateFpc            : $updateFpc,
            eventManager         : self::createStub(ManagerInterface::class),
        ))->updatePolicies($policies);
    }

    #[Test]
    public function policiesAreNotUpdatedIfHashDoesMatch(): void
    {
        $policies = [new Policy('script-src', '*.integer-net.de')];

        $flag = new SansecWatchFlag(
            hash('sha256', serialize($policies)),
            new DateTimeImmutable(),
            new DateTimeImmutable(),
        );

        $flagManagerStub = self::createStub(FlagManager::class);
        $flagManagerStub->method('getFlagData')->willReturn([]);

        $flagDataMapperStub = self::createStub(SansecWatchFlagMapper::class);
        $flagDataMapperStub->method('map')->willReturn($flag);

        $updatePoliciesCommand = $this->createMock(UpdatePoliciesCommand::class);
        $updatePoliciesCommand->expects($this->never())->method('execute');

        /** @noinspection PhpUnhandledExceptionInspection */
        (new PolicyUpdater(
            flagDataMapper       : $flagDataMapperStub,
            flagManager          : $flagManagerStub,
            updatePoliciesCommand: $updatePoliciesCommand,
            clock                : self::createStub(ClockInterface::class),
            updateFpc            : self::createStub(UpdateFpc::class),
            eventManager         : self::createStub(ManagerInterface::class),
        ))->updatePolicies($policies);
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    #[Test]
    public function lastCheckedUpIsUpdatedWhenHashMatches(): void
    {
        $policies  = [new Policy('script-src', '*.integer-net.de')];
        $hash      = hash('sha256', serialize($policies));
        $now       = new DateTimeImmutable();
        $yesterday = $now->sub(DateInterval::createFromDateString('1 day'));
        $flag      = new SansecWatchFlag($hash, $now, $yesterday);

        $clock = self::createStub(ClockInterface::class);
        $clock->method('now')->willReturn($now);

        $flagDataMapperStub = self::createStub(SansecWatchFlagMapper::class);
        $flagDataMapperStub->method('map')->willReturn($flag);

        $flagManager = $this->createMock(FlagManager::class);
        $flagManager->method('getFlagData')->willReturn([]);
        $flagManager
            ->expects($this->once())
            ->method('saveFlag')
            ->with(
                SansecWatchFlag::CODE,
                [
                    'hash'            => $hash,
                    'last_checked_at' => $now->format(DATE_ATOM),
                    'last_updated_at' => $yesterday->format(DATE_ATOM),
                ],
            );

        /** @noinspection PhpUnhandledExceptionInspection */
        (new PolicyUpdater(
            flagDataMapper       : $flagDataMapperStub,
            flagManager          : $flagManager,
            updatePoliciesCommand: self::createStub(UpdatePoliciesCommand::class),
            clock                : $clock,
            updateFpc            : self::createStub(UpdateFpc::class),
            eventManager         : self::createStub(ManagerInterface::class),
        ))->updatePolicies($policies);
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    #[Test]
    public function flagDataIsAlwaysUpdatedIfForceIsTrue(): void
    {
        $policies  = [new Policy('script-src', '*.integer-net.de')];
        $hash      = hash('sha256', serialize($policies));
        $now       = new DateTimeImmutable();
        $yesterday = $now->sub(DateInterval::createFromDateString('1 day'));

        $clock = self::createStub(ClockInterface::class);
        $clock->method('now')->willReturn($now);

        $flag = new SansecWatchFlag($hash, $now, $yesterday);

        $flagDataMapperStub = self::createStub(SansecWatchFlagMapper::class);
        $flagDataMapperStub->method('map')->willReturn($flag);

        $updatePoliciesCommand = $this->createMock(UpdatePoliciesCommand::class);
        $updatePoliciesCommand->expects($this->once())->method('execute')->with($policies);

        $flagManager = $this->createMock(FlagManager::class);
        $flagManager->method('getFlagData')->willReturn([]);
        $flagManager
            ->expects($this->once())
            ->method('saveFlag')
            ->with(
                SansecWatchFlag::CODE,
                [
                    'hash'            => $hash,
                    'last_checked_at' => $now->format(DATE_ATOM),
                    'last_updated_at' => $now->format(DATE_ATOM),
                ],
            );

        /** @noinspection PhpUnhandledExceptionInspection */
        (new PolicyUpdater(
            flagDataMapper       : $flagDataMapperStub,
            flagManager          : $flagManager,
            updatePoliciesCommand: $updatePoliciesCommand,
            clock                : $clock,
            updateFpc            : self::createStub(UpdateFpc::class),
            eventManager         : self::createStub(ManagerInterface::class),
        ))->updatePolicies($policies, true);
    }
}
