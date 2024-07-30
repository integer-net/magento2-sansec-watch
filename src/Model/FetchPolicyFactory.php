<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model;

use IntegerNet\SansecWatch\Model\DTO\Policy;
use Magento\Csp\Model\Policy\FetchPolicy;

class FetchPolicyFactory
{
    public function fromPolicyDto(Policy $policy): FetchPolicy
    {
        return new FetchPolicy(
            id: $policy->directive,
            noneAllowed: false,
            hostSources: [$policy->host],
            schemeSources: [],
            selfAllowed: true,
            inlineAllowed: true,
            evalAllowed: true,
            nonceValues: [],
            hashValues: [],
            dynamicAllowed: true,
            eventHandlersAllowed: true,
        );
    }
}
