<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Log;
use App\Models\Transaction;
use App\Models\Transfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_transfer_belongs_to_an_account_from()
    {
        $transfer = Transfer::factory()->create();

        $this->assertInstanceOf(Account::class, $transfer->fromAccount);
    }

    /** @test */
    public function a_transfer_belongs_to_an_account_to()
    {
        $transfer = Transfer::factory()->create();

        $this->assertInstanceOf(Account::class, $transfer->toAccount);
    }

    /** @test */
    public function a_transfer_belongs_to_an_admin()
    {
        $transfer = Transfer::factory()->create();

        $this->assertInstanceOf(Admin::class, $transfer->admin);
    }

    /** @test */
    public function adding_transfers_updates_both_account_balances()
    {
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        $category = Category::factory()->income()->create();

        Transaction::factory()->create([
            'account_id'  => $account1->id,
            'category_id' => $category->id,
            'amount'      => 100
        ]);

        $account1 = $account1->fresh();

        $this->assertEquals(100, $account1->balance);

        Transfer::factory()->create([
            'from_account' => $account1->fresh()->id,
            'to_account'   => $account2->id,
            'amount'       => 25
        ]);

        $this->assertEquals(75, $account1->fresh()->balance);
        $this->assertEquals(25, $account2->fresh()->balance);
    }

    /** @test */
    public function deleting_a_transfer_updates_account_balance()
    {
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();

        Transaction::factory()->create([
            'account_id'  => $account1->id,
            'category_id' => Category::factory()->income(),
            'amount'      => 100
        ]);

        $transfer = Transfer::factory()->create([
            'from_account' => $account1->id,
            'to_account'   => $account2->id,
            'amount'       => 20
        ]);

        $this->assertEquals(80, $account1->fresh()->balance);
        $this->assertEquals(20, $account2->fresh()->balance);

        $transfer->delete();

        $this->assertEquals(100, $account1->fresh()->balance);
        $this->assertEquals(0, $account2->fresh()->balance);
    }

    /** @test */
    public function updating_transfers_updates_all_account_balances()
    {
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        $account3 = Account::factory()->create();
        $incomeCategory = Category::factory()->income()->create();

        Transaction::factory()->create([
            'account_id'  => $account1->id,
            'category_id' => $incomeCategory->id,
            'amount'      => 100
        ]);

        $transfer = Transfer::factory()->create([
            'from_account' => $account1->id,
            'to_account'   => $account2->id,
            'amount'       => 20
        ]);

        $this->assertEquals(80, $account1->fresh()->balance);
        $this->assertEquals(20, $account2->fresh()->balance);
        $this->assertEquals(0, $account3->fresh()->balance);

        $transfer->update([
            'from_account' => $account1->id,
            'to_account'   => $account3->id,
            'amount'       => 25
        ]);

        $this->assertEquals(75, $account1->fresh()->balance);
        $this->assertEquals(0, $account2->fresh()->balance);
        $this->assertEquals(25, $account3->fresh()->balance);
    }

    /** @test */
    public function updating_transfers_when_to_and_from_account_are_changed_updates_all_account_balances_respectively()
    {
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        $account3 = Account::factory()->create();
        $account4 = Account::factory()->create();
        $incomeCategory = Category::factory()->income()->create();

        Transaction::factory()->create([
            'account_id'  => $account1->id,
            'category_id' => $incomeCategory->id,
            'amount'      => 100
        ]);

        Transaction::factory()->create([
            'account_id'  => $account4->id,
            'category_id' => $incomeCategory->id,
            'amount'      => 30
        ]);

        $transfer = Transfer::factory()->create([
            'from_account' => $account1->id,
            'to_account'   => $account2->id,
            'amount'       => 20
        ]);

        $this->assertEquals(80, $account1->fresh()->balance);
        $this->assertEquals(20, $account2->fresh()->balance);
        $this->assertEquals(0, $account3->fresh()->balance);
        $this->assertEquals(30, $account4->fresh()->balance);

        $transfer->update([
            'from_account' => $account4->id,
            'to_account'   => $account3->id,
            'amount'       => 5
        ]);

        $this->assertEquals(100, $account1->fresh()->balance);
        $this->assertEquals(0, $account2->fresh()->balance);
        $this->assertEquals(5, $account3->fresh()->balance);
        $this->assertEquals(25, $account4->fresh()->balance);
    }

    /** @test */
    public function log_is_created_when_a_transfer_gets_added()
    {
        // check factory creates - 2 accounts to transfer to and from
        $transfer = Transfer::factory()->create();

        $this->assertDatabaseCount('logs', 3);

        tap(Log::find(3), function ($log) use ($transfer) {
            $this->assertEquals('created', $log->action);
            $this->assertEquals('Transfer', $log->loggable_type);

            $changes = json_decode($log->changes, true);

            $this->assertEquals([
                'from_account' => $transfer->from_account,
                'to_account'   => $transfer->to_account,
                'amount'       => $transfer->amount,
                'description'  => $transfer->description,
                'date'         => $transfer->formattedDate
            ], $changes);
        });
    }

    /** @test */
    public function log_is_created_when_a_transfer_gets_updated()
    {
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        $account3 = Account::factory()->create();

        $transfer = Transfer::factory()->create([
            'from_account' => $account1->id,
            'to_account'   => $account2->id,
            'amount'       => 50,
            'description'  => 'transfer description',
        ]);

        $transfer->update([
            'to_account'  => $account3->id,
            'amount'      => 100,
            'description' => 'new transfer description',
            'notes'       => 'new transfer notes',
        ]);

        $this->assertDatabaseCount('logs', 5);

        tap(Log::find(5),
            function ($log) use ($account2, $account3) {
                $this->assertEquals('updated', $log->action);
                $this->assertEquals('Transfer', $log->loggable_type);

                $changes = json_decode($log->changes, true);

                $this->assertEquals([
                    'before' => [
                        'to_account'  => $account2->id,
                        'amount'      => 50,
                        'description' => 'transfer description',
                    ],
                    'after'  => [
                        'to_account'  => $account3->id,
                        'amount'      => 100,
                        'description' => 'new transfer description',
                        'notes'       => 'new transfer notes',
                    ]
                ], $changes);
            });
    }

    /** @test */
    public function log_is_created_when_a_transfer_gets_deleted()
    {
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();

        $transfer = Transfer::factory()->create([
            'from_account' => $account1->id,
            'to_account'   => $account2->id,
            'amount'       => 50,
            'description'  => 'description',
            'notes'        => 'notes',
        ]);

        $date = $transfer->formattedDate;

        $transfer->delete();

        $this->assertDatabaseCount('logs', 4);

        tap(Log::find(4), function ($log) use ($account1, $account2, $date) {
            $this->assertEquals('deleted', $log->action);
            $this->assertEquals('Transfer', $log->loggable_type);

            $changes = json_decode($log->changes, true);

            $this->assertEquals([
                'from_account' => $account1->id,
                'to_account'   => $account2->id,
                'amount'       => 50,
                'description'  => 'description',
                'notes'        => 'notes',
                'date'         => $date
            ], $changes);
        });
    }
}
