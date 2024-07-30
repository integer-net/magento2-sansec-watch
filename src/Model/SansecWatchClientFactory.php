<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model;

use Symfony\Component\HttpClient\HttpClient;

class SansecWatchClientFactory
{
    public function create(): SansecWatchClient
    {
        return new SansecWatchClient(HttpClient::create());
    }
}
