<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model;

use IntegerNet\SansecWatch\Mapper\PolicyMapper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SansecWatchClientFactory
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
    ) {
    }

    public function create(
        ?HttpClientInterface $httpClient = null,
        ?PolicyMapper $policyMapper = null,
        ?ManagerInterface $eventManager = null,
        ?Config $config = null
    ): SansecWatchClient {
        return new SansecWatchClient(
            $httpClient ?? HttpClient::create(),
            $policyMapper ?? new PolicyMapper(),
            $eventManager ?? $this->getEventManager(),
            $config ?? $this->getConfig(),
        );
    }

    private function getEventManager(): ManagerInterface
    {
        return $this->objectManager->get(ManagerInterface::class);
    }

    private function getConfig(): Config
    {
        return $this->objectManager->get(Config::class);
    }
}
