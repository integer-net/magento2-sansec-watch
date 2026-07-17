<?php /** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Test\Model;

use IntegerNet\SansecWatch\Model\Config;
use IntegerNet\SansecWatch\Model\Directive;
use IntegerNet\SansecWatch\Model\DirectiveFlag;
use IntegerNet\SansecWatch\Model\DTO\Policy;
use IntegerNet\SansecWatch\Model\FetchPolicyFactory;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FetchPolicyFactory::class)]
#[AllowMockObjectsWithoutExpectations]
class FetchPolicyFactoryTest extends TestCase
{
    /**
     * @param list<DirectiveFlag> $supportedFlags
     */
    #[Test]
    #[DataProvider('directiveProvider')]
    public function createsPolicyWithApplicableFlags(Directive $directive, array $supportedFlags): void
    {
        $config = self::createStub(Config::class);
        $config
            ->method('getDirectiveSetting')
            ->willReturn(true);

        /** @noinspection PhpUnhandledExceptionInspection */
        $policy = (new FetchPolicyFactory($config))->fromPolicyDto(
            new Policy($directive->value, 'cdn.example.test'),
        );

        self::assertSame($directive->value, $policy->getId());
        self::assertSame(['cdn.example.test'], $policy->getHostSources());
        self::assertTrue($policy->isNoneAllowed());
        self::assertTrue($policy->isSelfAllowed());
        self::assertSame(
            in_array(DirectiveFlag::InlineAllowed, $supportedFlags, true),
            $policy->isInlineAllowed(),
        );
        self::assertSame(
            in_array(DirectiveFlag::EvalAllowed, $supportedFlags, true),
            $policy->isEvalAllowed(),
        );
        self::assertSame(
            in_array(DirectiveFlag::DynamicAllowed, $supportedFlags, true),
            $policy->isDynamicAllowed(),
        );
        self::assertSame(
            in_array(DirectiveFlag::EventHandlersAllowed, $supportedFlags, true),
            $policy->areEventHandlersAllowed(),
        );
    }

    #[Test]
    public function rejectsUnsupportedDirective(): void
    {
        $config  = self::createStub(Config::class);
        $factory = new FetchPolicyFactory($config);

        $this->expectException(LocalizedException::class);
        $factory->fromPolicyDto(new Policy('unsupported-src', 'example.test'));
    }

    /**
     * @return iterable<string, array{Directive, list<DirectiveFlag>}>
     */
    public static function directiveProvider(): iterable
    {
        $sourceFlags      = [
            DirectiveFlag::NoneAllowed,
            DirectiveFlag::SelfAllowed,
        ];
        $scriptStyleFlags = [
            ...$sourceFlags,
            DirectiveFlag::InlineAllowed,
            DirectiveFlag::EvalAllowed,
            DirectiveFlag::DynamicAllowed,
            DirectiveFlag::EventHandlersAllowed,
        ];

        foreach ([
                     Directive::DefaultSrc,
                     Directive::ChildSrc,
                     Directive::ConnectSrc,
                     Directive::FontSrc,
                     Directive::FrameSrc,
                     Directive::ImgSrc,
                     Directive::ManifestSrc,
                     Directive::MediaSrc,
                     Directive::ObjectSrc,
                     Directive::BaseUri,
                     Directive::FormAction,
                     Directive::FrameAncestors,
                 ] as $directive) {
            yield $directive->name => [$directive, $sourceFlags];
        }

        yield 'script-src' => [Directive::ScriptSrc, $scriptStyleFlags];
        yield 'style-src' => [Directive::StyleSrc, $scriptStyleFlags];
    }
}
