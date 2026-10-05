<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Test\Model\Command;

use IntegerNet\SansecWatch\Model\DTO\Policy;
use IntegerNet\SansecWatch\Model\Query\GetAllPolicies;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(GetAllPolicies::class)]
class GetAllPoliciesTest extends TestCase
{
    private function createSelectStub(): Select
    {
        $select = self::createStub(Select::class);
        $select->method('from')
            ->willReturnSelf();

        return $select;
    }

    private function createGetAllPoliciesQuery(AdapterInterface $connection): GetAllPolicies
    {
        $resourceConnection = self::createStub(ResourceConnection::class);
        $resourceConnection
            ->method('getConnection')
            ->willReturn($connection);

        return new GetAllPolicies($resourceConnection);
    }

    #[Test]
    public function returnEmptyListIfAnyExceptionIsThrown(): void
    {
        $connection = self::createStub(AdapterInterface::class);
        $connection
            ->method('select')
            ->willReturn($this->createSelectStub());
        $connection
            ->method('fetchAll')
            ->willThrowException(new \Exception('Something went wrong'));

        $getAllPoliciesQuery = $this->createGetAllPoliciesQuery($connection);

        self::assertCount(0, $getAllPoliciesQuery->execute());
    }

    #[Test]
    public function usesStorePoliciesAndCachesEachStoreSeparately(): void
    {
        $connection = self::createMock(AdapterInterface::class);
        $connection
            ->method('select')
            ->willReturn($this->createSelectStub());
        $connection
            ->expects(self::exactly(2))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [['directive' => 'script-src', 'host' => 'store-one.example']],
                [['directive' => 'script-src', 'host' => 'store-two.example']],
            );
        $getAllPoliciesQuery = $this->createGetAllPoliciesQuery($connection);

        $storeOnePolicies = $getAllPoliciesQuery->execute(1);
        $storeTwoPolicies = $getAllPoliciesQuery->execute(2);
        $cachedStoreOnePolicies = $getAllPoliciesQuery->execute(1);

        self::assertEquals([new Policy('script-src', 'store-one.example')], $storeOnePolicies);
        self::assertEquals([new Policy('script-src', 'store-two.example')], $storeTwoPolicies);
        self::assertEquals($storeOnePolicies, $cachedStoreOnePolicies);
    }

    #[Test]
    public function fallsBackToDefaultPoliciesWhenStoreHasNoPolicies(): void
    {
        $connection = self::createMock(AdapterInterface::class);
        $connection
            ->method('select')
            ->willReturn($this->createSelectStub());
        $connection
            ->expects(self::exactly(2))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [],
                [['directive' => 'img-src', 'host' => 'default.example']],
            );
        $getAllPoliciesQuery = $this->createGetAllPoliciesQuery($connection);

        self::assertEquals(
            [new Policy('img-src', 'default.example')],
            $getAllPoliciesQuery->execute(3),
        );
    }
}
