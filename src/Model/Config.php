<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model;

use Symfony\Component\Uid\Uuid;

class Config
{
    public const POLICY_TABLE = 'integernet_sansecwatch_policies';

    public function getUuid(): Uuid
    {
        // TODO: create configuration
        // TODO: read from config
        return Uuid::fromString('685769a2-38a4-4d06-a19a-67a528197f51');
    }
}
