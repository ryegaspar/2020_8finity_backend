<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\Category;
use App\Models\Check;
use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function log_is_created_when_a_check_gets_added()
    {
        // check factory creates - category and account
        $check = Check::factory()->create();

        $this->assertDatabaseCount('logs', 3);

        tap(Log::find(3), function ($log) use ($check) {
            $this->assertEquals('created', $log->action);
            $this->assertEquals('Check', $log->loggable_type);

            $changes = json_decode($log->changes, true);

            $this->assertEquals([
                'category_id' => $check->category_id,
                'account_id'  => $check->account_id,
                'amount'      => $check->amount,
                'due_date'    => $check->formattedDueDate,
            ], $changes);
        });
    }

    /** @test */
    public function log_is_created_when_a_check_gets_updated()
    {
        $category = Category::factory()->create();
        $account = Account::factory()->create();

        $check = Check::factory()->create([
            'category_id' => $category->id,
            'account_id'  => $account->id,
            'amount'      => 50,
            'description' => 'old description',
            'status'      => 'pending',
            'notes'       => 'old notes',
        ]);

        $check->update([
            'amount'      => 100,
            'description' => 'new description',
            'status'      => 'cancelled',
            'notes'       => 'new notes',
        ]);

        $this->assertDatabaseCount('logs', 4);

        tap(Log::find(4),
            function ($log) {
                $this->assertEquals('updated', $log->action);
                $this->assertEquals('Check', $log->loggable_type);

                $changes = json_decode($log->changes, true);

                $this->assertEquals([
                    'before' => [
                        'amount'      => 50,
                        'description' => 'old description',
                        'status'      => 'pending',
                        'notes'       => 'old notes',
                    ],
                    'after'  => [
                        'amount'      => 100,
                        'description' => 'new description',
                        'status'      => 'cancelled',
                        'notes'       => 'new notes',
                    ]
                ], $changes);
            });
    }

    /** @test */
    public function log_is_created_when_a_check_gets_deleted()
    {
        $category = Category::factory()->create();
        $account = Account::factory()->create();

        $check = Check::factory()->create([
            'category_id' => $category->id,
            'account_id'  => $account->id,
            'amount'      => 50,
            'description' => 'old description',
            'status'      => 'pending',
            'notes'       => 'old notes',
        ]);

        $date = $check->formattedDueDate;

        $check->delete();

        $this->assertDatabaseCount('logs', 4);

        tap(Log::find(4), function ($log) use ($category, $account, $date) {
            $this->assertEquals('deleted', $log->action);
            $this->assertEquals('Check', $log->loggable_type);

            $changes = json_decode($log->changes, true);

            $this->assertEquals([
                'category_id' => $category->id,
                'account_id'  => $account->id,
                'amount'      => 50,
                'description' => 'old description',
                'status'      => 'pending',
                'notes'       => 'old notes',
                'due_date'    => $date
            ], $changes);
        });
    }
}
