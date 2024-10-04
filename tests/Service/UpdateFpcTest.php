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
    private Config&MockObject $config;
    private PageCacheConfig&Stub $pageCacheConfig;
    private TypeListInterface&MockObject $cacheTypeList;

    private UpdateFpc $updateFpc;

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    protected function setUp(): void
    {
        $this->config          = self::createMock(Config::class);
        $this->pageCacheConfig = self::createStub(PageCacheConfig::class);
        $this->cacheTypeList   = self::createMock(TypeListInterface::class);

        $this->updateFpc = new UpdateFpc(
            $this->config,
            $this->pageCacheConfig,
            $this->cacheTypeList,
        );
    }

    #[Test]
    public function nothingIsDoneIfFpcIsDisabled(): void
    {
        $this->pageCacheConfig
            ->method('isEnabled')
            ->willReturn(false);

        $this->config
            ->expects(self::never())
            ->method(self::anything());

        $this->updateFpc->execute();
    }

    #[Test]
    public function nothingIsDoneIfFpcModeIsNone(): void
    {
        $this->pageCacheConfig
            ->method('isEnabled')
            ->willReturn(true);

        $this->config
            ->expects(self::once())
            ->method('getFpcMode')
            ->willReturn(FpcMode::NONE);

        $this->cacheTypeList
            ->expects(self::never())
            ->method(self::anything());

        $this->updateFpc->execute();
    }

    #[Test]
    public function invalidatesFpcIfModeIfInvalidate(): void
    {
        $this->pageCacheConfig
            ->method('isEnabled')
            ->willReturn(true);

        $this->config
            ->expects(self::once())
            ->method('getFpcMode')
            ->willReturn(FpcMode::INVALIDATE);

        $this->cacheTypeList
            ->expects(self::once())
            ->method('invalidate');

        $this->updateFpc->execute();
    }

    #[Test]
    public function clearsFpcIfModeIfClear(): void
    {
        $this->pageCacheConfig
            ->method('isEnabled')
            ->willReturn(true);

        $this->config
            ->expects(self::once())
            ->method('getFpcMode')
            ->willReturn(FpcMode::CLEAR);

        $this->cacheTypeList
            ->expects(self::once())
            ->method('cleanType');

        $this->updateFpc->execute();
    }
}
