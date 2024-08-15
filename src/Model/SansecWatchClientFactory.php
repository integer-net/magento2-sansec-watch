<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model;

use IntegerNet\SansecWatch\Mapper\PolicyMapper;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SansecWatchClientFactory
{
    public function create(
        ?HttpClientInterface $httpClient = null,
        ?PolicyMapper $policyMapper = null,
    ): SansecWatchClient {
        return new SansecWatchClient(
            $httpClient ?? HttpClient::create(),
            $policyMapper ?? new PolicyMapper(),
        );
    }
}
