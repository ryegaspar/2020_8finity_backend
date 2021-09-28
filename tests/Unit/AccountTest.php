<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\Check;
use App\Models\Log;
use App\Models\Transaction;
use App\Models\Transfer;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_account_has_many_to_transfers_from()
    {
        $account = Account::factory()->create();
        Transfer::factory()->create(['from_account' => $account->id]);

        $this->assertEquals(1, $account->fromTransfers()->count());
        $this->assertInstanceOf(HasMany::class, $account->fromTransfers());
    }

    /** @test */
    public function an_account_has_many_to_transfers_to()
    {
        $account = Account::factory()->create();
        Transfer::factory()->create(['to_account' => $account->id]);

        $this->assertEquals(1, $account->toTransfers()->count());
        $this->assertInstanceOf(HasMany::class, $account->toTransfers());
    }

    /** @test */
    public function an_account_has_many_to_transactions()
    {
        $account = Account::factory()->create();
        Transaction::factory()->create(['account_id' => $account->id]);

        $this->assertEquals(1, $account->transactions()->count());
        $this->assertInstanceOf(HasMany::class, $account->transactions());
    }

    /** @test */
    public function an_account_has_many_to_checks()
    {
        $account = Account::factory()->create();
        Check::factory()->create(['account_id' => $account->id]);

        $this->assertEquals(1, $account->checks()->count());
        $this->assertInstanceOf(HasMany::class, $account->checks());
    }

    /** @test */
    public function log_is_created_when_an_account_gets_added()
    {
        $account = Account::factory()->create();

        $this->assertDatabaseCount('logs', 1);

        tap(Log::first(), function ($log) use ($account) {
            $this->assertEquals('created', $log->action);
            $this->assertEquals('Account', $log->loggable_type);

            $changes = json_decode($log->changes, true);

            $this->assertEquals([
                'name'      => $account->name,
                'is_active' => true
            ], $changes);
        });
    }

    /** @test */
    public function log_is_created_when_an_account_gets_updated()
    {
        $account = Account::factory()->create(['name' => 'old']);

        $account->update(['name' => 'new', 'is_active' => false]);

        $this->assertDatabaseCount('logs', 2);

        tap(Log::find(2), function ($log) {
            $this->assertEquals('updated', $log->action);
            $this->assertEquals('Account', $log->loggable_type);

            $changes = json_decode($log->changes, true);

            $this->assertEquals([
                'before' => [
                    'name'      => 'old',
                    'is_active' => true
                ],
                'after'  => [
                    'name'      => 'new',
                    'is_active' => false
                ]
            ], $changes);
        });
    }

    /** @test */
    public function log_is_created_when_an_account_gets_deleted()
    {
        $account = Account::factory()->create();

        $account->delete();

        $this->assertDatabaseCount('logs', 2);

        tap(Log::find(2), function ($log) use ($account) {
            $this->assertEquals('deleted', $log->action);
            $this->assertEquals('Account', $log->loggable_type);

            $changes = json_decode($log->changes, true);

            $this->assertEquals([
                'name'      => $account->name,
                'is_active' => true
            ], $changes);
        });
    }
}
