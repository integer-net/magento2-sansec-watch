<?php
/** @noinspection DuplicatedCode */

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Test\Service;

use IntegerNet\SansecWatch\Model\Config;
use IntegerNet\SansecWatch\Model\FpcMode;
use IntegerNet\SansecWatch\Service\UpdateFpc;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\PageCache\Model\Config as PageCacheConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[CoversClass(UpdateFpc::class)]
class UpdateFpcTest extends TestCase
{
    #[Test]
    public function nothingIsDoneIfFpcIsDisabled(): void
    {
        $pageCacheConfig = self::createStub(PageCacheConfig::class);
        $pageCacheConfig->method('isEnabled')->willReturn(false);

        $config = $this->createMock(Config::class);
        $config->expects($this->never())->method(self::anything());

        (new UpdateFpc(
            $config,
            $pageCacheConfig,
            self::createStub(TypeListInterface::class),
        ))->execute();
    }

    #[Test]
    public function nothingIsDoneIfFpcModeIsNone(): void
    {
        $pageCacheConfig = self::createStub(PageCacheConfig::class);
        $pageCacheConfig->method('isEnabled')->willReturn(true);

        $config = $this->createMock(Config::class);
        $config->expects($this->once())->method('getFpcMode')->willReturn(FpcMode::None);

        $cacheTypeList = $this->createMock(TypeListInterface::class);
        $cacheTypeList->expects($this->never())->method(self::anything());

        (new UpdateFpc(
            $config,
            $pageCacheConfig,
            $cacheTypeList,
        ))->execute();
    }

    #[Test]
    public function invalidatesFpcIfModeIfInvalidate(): void
    {
        $pageCacheConfig = self::createStub(PageCacheConfig::class);
        $pageCacheConfig->method('isEnabled')->willReturn(true);

        $config = $this->createMock(Config::class);
        $config->expects($this->once())->method('getFpcMode')->willReturn(FpcMode::Invalidate);

        $cacheTypeList = $this->createMock(TypeListInterface::class);
        $cacheTypeList->expects($this->once())->method('invalidate');

        (new UpdateFpc(
            $config,
            $pageCacheConfig,
            $cacheTypeList,
        ))->execute();
    }

    #[Test]
    public function clearsFpcIfModeIfClear(): void
    {
        $pageCacheConfig = self::createStub(PageCacheConfig::class);
        $pageCacheConfig->method('isEnabled')->willReturn(true);

        $config = $this->createMock(Config::class);
        $config->expects($this->once())->method('getFpcMode')->willReturn(FpcMode::Clear);

        $cacheTypeList = $this->createMock(TypeListInterface::class);
        $cacheTypeList->expects($this->once())->method('cleanType');

        (new UpdateFpc(
            $config,
            $pageCacheConfig,
            $cacheTypeList,
        ))->execute();
    }
}
