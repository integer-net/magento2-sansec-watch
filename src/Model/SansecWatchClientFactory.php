<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SansecWatchClientFactory
{
    public function create(?HttpClientInterface $httpClient = null): SansecWatchClient
    {
        return new SansecWatchClient($httpClient ?? HttpClient::create());
    }
}
