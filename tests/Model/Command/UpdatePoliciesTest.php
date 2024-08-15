<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Test\Model\Command;

use IntegerNet\SansecWatch\Model\Command\UpdatePolicies;
use IntegerNet\SansecWatch\Model\Exception\CouldNotUpdatePoliciesException;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(UpdatePolicies::class)]
class UpdatePoliciesTest extends TestCase
{
    private AdapterInterface&MockObject $connection;

    private UpdatePolicies $updatePoliciesCommand;

    /** @noinspection PhpUnhandledExceptionInspection */
    protected function setUp(): void
    {
        $this->connection = self::createMock(AdapterInterface::class);

        $resourceConnection = self::createStub(ResourceConnection::class);
        $resourceConnection
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->updatePoliciesCommand = new UpdatePolicies($resourceConnection);
    }

    #[Test]
    public function doesNotExecuteInsertWhenNoPoliciesAreGiven(): void
    {
        $this->connection
            ->expects(self::never())
            ->method('insertMultiple');

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->updatePoliciesCommand->execute([]);
    }

    #[Test]
    public function deletesExistingRulesBeforeUpdating(): void
    {
        $this->connection
            ->expects(self::once())
            ->method('delete');

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->updatePoliciesCommand->execute([]);
    }

    #[Test]
    public function databaseQueriesAreRunInTransaction(): void
    {
        $this->connection
            ->expects(self::once())
            ->method('beginTransaction');

        $this->connection
            ->expects(self::once())
            ->method('commit');

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->updatePoliciesCommand->execute([]);
    }

    #[Test]
    public function transactionIsRolledBackInCaseOfAnyError(): void
    {
        self::expectException(CouldNotUpdatePoliciesException::class);

        $this->connection
            ->expects(self::once())
            ->method('beginTransaction');

        $this->connection
            ->method('commit')
            ->willThrowException(new \Exception('Something went wrong'));

        $this->connection
            ->expects(self::once())
            ->method('rollBack');

        $this->updatePoliciesCommand->execute([]);
    }
}
