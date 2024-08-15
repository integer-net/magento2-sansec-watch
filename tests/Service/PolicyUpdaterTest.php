<?php /** @noinspection DuplicatedCode */

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Test\Service;

use DateTimeImmutable;
use DateInterval;
use IntegerNet\SansecWatch\Mapper\SansecWatchFlagMapper;
use IntegerNet\SansecWatch\Model\Command\UpdatePolicies;
use IntegerNet\SansecWatch\Model\DTO\Policy;
use IntegerNet\SansecWatch\Model\DTO\SansecWatchFlag;
use IntegerNet\SansecWatch\Service\PolicyUpdater;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\FlagManager;
use Magento\PageCache\Model\Cache\Type;
use Magento\PageCache\Model\Config as PageCacheConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
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
    private PageCacheConfig&Stub $pageCacheConfig;
    private ClockInterface&Stub $clock;
    private TypeListInterface&MockObject $cacheList;

    private PolicyUpdater $policyUpdater;

    /** @noinspection PhpUnhandledExceptionInspection */
    protected function setUp(): void
    {
        $this->flagDataMapper = self::createStub(SansecWatchFlagMapper::class);
        $this->clock = self::createStub(ClockInterface::class);
        $this->flagManager = self::createMock(FlagManager::class);
        $this->updatePolicies = self::createMock(UpdatePolicies::class);
        $this->pageCacheConfig = self::createStub(PageCacheConfig::class);
        $this->cacheList = self::createMock(TypeListInterface::class);

        $this->policyUpdater = new PolicyUpdater(
            $this->flagDataMapper,
            $this->flagManager,
            $this->updatePolicies,
            $this->pageCacheConfig,
            $this->cacheList,
            $this->clock,
        );
    }

    #[Test]
    #[TestWith([false])]
    #[TestWith([true])]
    public function pageCacheIsClearedIfItIsEnabled(bool $isPageCacheEnabled): void
    {
        $this->pageCacheConfig
            ->method('isEnabled')
            ->willReturn($isPageCacheEnabled);

        $this->cacheList
            ->expects(
                $isPageCacheEnabled
                    ? self::once()
                    : self::never()
            )
            ->method('invalidate')
            ->with(Type::TYPE_IDENTIFIER);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->policyUpdater->updatePolicies([], true);
    }

    #[Test]
    public function policiesAreNotUpdatedIfHashDoesMatch(): void
    {
        $policies = [new Policy('script-src', '*.integer-net.de')];
        $hash = hash('sha256', serialize($policies));

        $this->flagManager
            ->method('getFlagData')
            ->willReturn([
                'hash' => $hash,
                'lastCheckedAt' => new DateTimeImmutable(),
                'lastUpdatedAt' => new DateTimeImmutable(),
            ]);

        $this->flagDataMapper
            ->method('map')
            ->willReturn(new SansecWatchFlag(
                $hash,
                new DateTimeImmutable(),
                new DateTimeImmutable(),
            ));

        $this->updatePolicies
            ->expects(self::never())
            ->method('execute');

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->policyUpdater->updatePolicies($policies);
    }

    #[Test]
    public function lastCheckedUpIsUpdatedWhenHashMatches(): void
    {
        $policies = [new Policy('script-src', '*.integer-net.de')];
        $hash = hash('sha256', serialize($policies));
        $now = new DateTimeImmutable();
        $yesterday = $now->sub(DateInterval::createFromDateString('1 day'));

        $this->clock
            ->method('now')
            ->willReturn($now);

        $this->flagManager
            ->method('getFlagData')
            ->willReturn([
                'hash' => $hash,
                'lastCheckedAt' => $yesterday,
                'lastUpdatedAt' => $yesterday,
            ]);

        $this->flagDataMapper
            ->method('map')
            ->willReturn(new SansecWatchFlag($hash, $now, $yesterday));

        $this->flagManager
            ->expects(self::once())
            ->method('saveFlag')
            ->with(
                SansecWatchFlag::CODE,
                [
                    'hash' => $hash,
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
        $policies = [new Policy('script-src', '*.integer-net.de')];
        $hash = hash('sha256', serialize($policies));
        $now = new DateTimeImmutable();
        $yesterday = $now->sub(DateInterval::createFromDateString('1 day'));

        $this->clock
            ->method('now')
            ->willReturn($now);

        $this->flagManager
            ->method('getFlagData')
            ->willReturn([
                'hash' => $hash,
                'lastCheckedAt' => $yesterday,
                'lastUpdatedAt' => $yesterday,
            ]);

        $this->flagDataMapper
            ->method('map')
            ->willReturn(new SansecWatchFlag($hash, $now, $yesterday));

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
                    'hash' => $hash,
                    'last_checked_at' => $now->format(DATE_ATOM),
                    'last_updated_at' => $now->format(DATE_ATOM),
                ]
            );

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->policyUpdater->updatePolicies($policies, true);
    }
}
