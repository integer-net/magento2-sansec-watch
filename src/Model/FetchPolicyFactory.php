<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model;

use IntegerNet\SansecWatch\Model\DTO\Policy;
use Magento\Csp\Model\Policy\FetchPolicy;

class FetchPolicyFactory
{
    public function __construct(
        private readonly bool $noneAllowed = false,
        private readonly bool $selfAllowed = true,
        private readonly bool $inlineAllowed = true,
        private readonly bool $evalAllowed = true,
        private readonly bool $dynamicAllowed = false,
        private readonly bool $eventHandlersAllowed = true,
    ) {
    }

    public function fromPolicyDto(Policy $policy): FetchPolicy
    {
        return new FetchPolicy(
            id: $policy->directive,
            noneAllowed: $this->noneAllowed,
            hostSources: [$policy->host],
            schemeSources: [],
            selfAllowed: $this->selfAllowed,
            inlineAllowed: $this->inlineAllowed,
            evalAllowed: $this->evalAllowed,
            nonceValues: [],
            hashValues: [],
            dynamicAllowed: $this->dynamicAllowed,
            eventHandlersAllowed: $this->eventHandlersAllowed,
        );
    }
}
