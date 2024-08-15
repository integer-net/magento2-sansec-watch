<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Test\Model\Command;

use IntegerNet\SansecWatch\Model\Query\GetAllPolicies;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[CoversClass(GetAllPolicies::class)]
class GetAllPoliciesTest extends TestCase
{
    private AdapterInterface&Stub $connection;

    private GetAllPolicies $getAllPoliciesQuery;

    /** @noinspection PhpUnhandledExceptionInspection */
    protected function setUp(): void
    {
        $select = self::createStub(Select::class);
        $this->connection = self::createStub(AdapterInterface::class);
        $this->connection
            ->method('select')
            ->willReturn($select);

        $resourceConnection = self::createStub(ResourceConnection::class);
        $resourceConnection
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->getAllPoliciesQuery = new GetAllPolicies($resourceConnection);
    }

    #[Test]
    public function returnEmptyListIfAnyExceptionIsThrown(): void
    {
        $this->connection
            ->method('fetchAll')
            ->willThrowException(new \Exception('Something went wrong'));

        self::assertCount(0, $this->getAllPoliciesQuery->execute());
    }
}
