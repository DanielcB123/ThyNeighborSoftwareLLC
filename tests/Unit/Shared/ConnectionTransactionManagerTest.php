<?php

namespace Tests\Unit\Shared;

use App\Shared\Database\ConnectionTransactionManager;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class ConnectionTransactionManagerTest extends TestCase
{
    public function test_central_transactions_run_on_central_connection(): void
    {
        $connection = Mockery::mock(ConnectionInterface::class);

        DB::shouldReceive('connection')
            ->once()
            ->with('central')
            ->andReturn($connection);

        $connection->shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn (callable $callback) => $callback());

        $result = ConnectionTransactionManager::central(fn () => 'central-result');

        $this->assertSame('central-result', $result);
    }

    public function test_tenant_transactions_run_on_tenant_connection(): void
    {
        $connection = Mockery::mock(ConnectionInterface::class);

        DB::shouldReceive('connection')
            ->once()
            ->with('tenant')
            ->andReturn($connection);

        $connection->shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn (callable $callback) => $callback());

        $result = ConnectionTransactionManager::tenant(fn () => 'tenant-result');

        $this->assertSame('tenant-result', $result);
    }
}
