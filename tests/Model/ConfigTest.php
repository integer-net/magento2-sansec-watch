<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Test\Model;

use IntegerNet\SansecWatch\Model\Config;
use IntegerNet\SansecWatch\Model\Directive;
use IntegerNet\SansecWatch\Model\DirectiveFlag;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Config::class)]
class ConfigTest extends TestCase
{
    #[Test]
    public function returnsExplicitDirectiveSetting(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->with(
                'integernet_sansecwatch/directive/script_src/inline_allowed',
                ScopeInterface::SCOPE_STORE,
                null,
            )
            ->willReturn('yes');

        $config = new Config($scopeConfig);

        /** @noinspection PhpUnhandledExceptionInspection */
        self::assertTrue($config->getDirectiveSetting(Directive::ScriptSrc, DirectiveFlag::InlineAllowed));
    }

    #[Test]
    public function returnsFalseForExplicitNoSetting(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->with(
                'integernet_sansecwatch/directive/img_src/self_allowed',
                ScopeInterface::SCOPE_STORE,
                null,
            )
            ->willReturn('no');

        $config = new Config($scopeConfig);

        /** @noinspection PhpUnhandledExceptionInspection */
        self::assertFalse($config->getDirectiveSetting(Directive::ImgSrc, DirectiveFlag::SelfAllowed));
    }

    #[Test]
    public function usesSameStoreIdForInheritedFallback(): void
    {
        $storeId = 42;
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->with(
                'integernet_sansecwatch/directive/style_src/inline_allowed',
                ScopeInterface::SCOPE_STORE,
                $storeId,
            )
            ->willReturn('inherited');
        $scopeConfig
            ->expects($this->once())
            ->method('isSetFlag')
            ->with(
                'integernet_sansecwatch/directive/inline_allowed',
                ScopeInterface::SCOPE_STORE,
                $storeId,
            )
            ->willReturn(true);

        $config = new Config($scopeConfig);

        /** @noinspection PhpUnhandledExceptionInspection */
        self::assertTrue($config->getDirectiveSetting(Directive::StyleSrc, DirectiveFlag::InlineAllowed, $storeId));
    }

    #[Test]
    public function throwsForInvalidDirectiveSetting(): void
    {
        $scopeConfig = self::createStub(ScopeConfigInterface::class);
        $scopeConfig
            ->method('getValue')
            ->willReturn('invalid');

        $config = new Config($scopeConfig);

        $this->expectException(LocalizedException::class);
        $config->getDirectiveSetting(Directive::ScriptSrc, DirectiveFlag::EvalAllowed);
    }
}
