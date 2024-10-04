<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model\Event;

use Assert\Assertion;
use Assert\InvalidArgumentException;
use IntegerNet\SansecWatch\Model\DTO\Policy;

class FetchedPolicies
{
    /**
     * @param list<Policy> $policies
     */
    public function __construct(
        private array $policies,
    ) {
        $this->validateArrayItems($policies);
    }

    /**
     * @param list<Policy> $policies
     *
     * @throws InvalidArgumentException
     */
    public function setPolicies(array $policies): void
    {
        $this->validateArrayItems($policies);

        $this->policies = $policies;
    }

    /**
     * @return list<Policy>
     */
    public function getPolicies(): array
    {
        return $this->policies;
    }

    /**
     * @param list<Policy> $policies
     *
     * @throws InvalidArgumentException
     */
    private function validateArrayItems(array $policies): void
    {
        Assertion::allIsInstanceOf($policies, Policy::class, 'All Items must be of type ' . Policy::class);
    }
}
