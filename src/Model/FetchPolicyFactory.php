<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model;

use IntegerNet\SansecWatch\Model\DTO\Policy;
use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\Framework\Exception\LocalizedException;

use function in_array;
use function __;

class FetchPolicyFactory
{
    private const array SOURCE_FLAGS = [
        DirectiveFlag::NoneAllowed,
        DirectiveFlag::SelfAllowed,
    ];

    private const array SCRIPT_STYLE_FLAGS = [
        DirectiveFlag::NoneAllowed,
        DirectiveFlag::SelfAllowed,
        DirectiveFlag::InlineAllowed,
        DirectiveFlag::EvalAllowed,
        DirectiveFlag::DynamicAllowed,
        DirectiveFlag::EventHandlersAllowed,
    ];

    public function __construct(
        private readonly Config $config,
    ) {}

    /**
     * @throws LocalizedException
     */
    public function fromPolicyDto(Policy $policy): FetchPolicy
    {
        $directive = Directive::tryFrom($policy->directive) ?? throw new LocalizedException(__('Unsupported CSP directive "%1"', $policy->directive));

        return match ($directive) {
            Directive::DefaultSrc     => $this->createDefaultSrcPolicy($policy),
            Directive::ChildSrc       => $this->createChildSrcPolicy($policy),
            Directive::ConnectSrc     => $this->createConnectSrcPolicy($policy),
            Directive::FontSrc        => $this->createFontSrcPolicy($policy),
            Directive::FrameSrc       => $this->createFrameSrcPolicy($policy),
            Directive::ImgSrc         => $this->createImgSrcPolicy($policy),
            Directive::ManifestSrc    => $this->createManifestSrcPolicy($policy),
            Directive::MediaSrc       => $this->createMediaSrcPolicy($policy),
            Directive::ObjectSrc      => $this->createObjectSrcPolicy($policy),
            Directive::ScriptSrc      => $this->createScriptSrcPolicy($policy),
            Directive::StyleSrc       => $this->createStyleSrcPolicy($policy),
            Directive::BaseUri        => $this->createBaseUriPolicy($policy),
            Directive::FormAction     => $this->createFormActionPolicy($policy),
            Directive::FrameAncestors => $this->createFrameAncestorsPolicy($policy),
        };
    }

    /**
     * @param list<DirectiveFlag> $supportedFlags
     */
    private function createFetchPolicy(
        Policy $policy,
        Directive $directive,
        array $supportedFlags,
    ): FetchPolicy {
        $isEnabled = fn(DirectiveFlag $flag): bool => in_array($flag, $supportedFlags, true)
            && $this->config->getDirectiveSetting($directive, $flag);

        return new FetchPolicy(
            id: $directive->value,
            noneAllowed: $isEnabled(DirectiveFlag::NoneAllowed),
            hostSources: [$policy->host],
            schemeSources: [],
            selfAllowed: $isEnabled(DirectiveFlag::SelfAllowed),
            inlineAllowed: $isEnabled(DirectiveFlag::InlineAllowed),
            evalAllowed: $isEnabled(DirectiveFlag::EvalAllowed),
            nonceValues: [],
            hashValues: [],
            dynamicAllowed: $isEnabled(DirectiveFlag::DynamicAllowed),
            eventHandlersAllowed: $isEnabled(DirectiveFlag::EventHandlersAllowed),
        );
    }

    private function createDefaultSrcPolicy(Policy $policy): FetchPolicy
    {
        return $this->createFetchPolicy($policy, Directive::DefaultSrc, self::SOURCE_FLAGS);
    }

    private function createChildSrcPolicy(Policy $policy): FetchPolicy
    {
        return $this->createFetchPolicy($policy, Directive::ChildSrc, self::SOURCE_FLAGS);
    }

    private function createConnectSrcPolicy(Policy $policy): FetchPolicy
    {
        return $this->createFetchPolicy($policy, Directive::ConnectSrc, self::SOURCE_FLAGS);
    }

    private function createFontSrcPolicy(Policy $policy): FetchPolicy
    {
        return $this->createFetchPolicy($policy, Directive::FontSrc, self::SOURCE_FLAGS);
    }

    private function createFrameSrcPolicy(Policy $policy): FetchPolicy
    {
        return $this->createFetchPolicy($policy, Directive::FrameSrc, self::SOURCE_FLAGS);
    }

    private function createImgSrcPolicy(Policy $policy): FetchPolicy
    {
        return $this->createFetchPolicy($policy, Directive::ImgSrc, self::SOURCE_FLAGS);
    }

    private function createManifestSrcPolicy(Policy $policy): FetchPolicy
    {
        return $this->createFetchPolicy($policy, Directive::ManifestSrc, self::SOURCE_FLAGS);
    }

    private function createMediaSrcPolicy(Policy $policy): FetchPolicy
    {
        return $this->createFetchPolicy($policy, Directive::MediaSrc, self::SOURCE_FLAGS);
    }

    private function createObjectSrcPolicy(Policy $policy): FetchPolicy
    {
        return $this->createFetchPolicy($policy, Directive::ObjectSrc, self::SOURCE_FLAGS);
    }

    private function createScriptSrcPolicy(Policy $policy): FetchPolicy
    {
        return $this->createFetchPolicy($policy, Directive::ScriptSrc, self::SCRIPT_STYLE_FLAGS);
    }

    private function createStyleSrcPolicy(Policy $policy): FetchPolicy
    {
        return $this->createFetchPolicy($policy, Directive::StyleSrc, self::SCRIPT_STYLE_FLAGS);
    }

    private function createBaseUriPolicy(Policy $policy): FetchPolicy
    {
        return $this->createFetchPolicy($policy, Directive::BaseUri, self::SOURCE_FLAGS);
    }

    private function createFormActionPolicy(Policy $policy): FetchPolicy
    {
        return $this->createFetchPolicy($policy, Directive::FormAction, self::SOURCE_FLAGS);
    }

    private function createFrameAncestorsPolicy(Policy $policy): FetchPolicy
    {
        return $this->createFetchPolicy($policy, Directive::FrameAncestors, self::SOURCE_FLAGS);
    }
}
