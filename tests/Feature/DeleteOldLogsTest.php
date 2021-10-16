<?php

namespace Tests\Feature;

use App\Models\Log;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteOldLogsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function deleting_old_logs_via_cli()
    {
        $delete_after = config('app.log_delete_after');

        Transaction::factory()->create();

        $this->assertEquals(3, Log::count());

        $this->artisan('8finity:delete-old-logs');
        $this->assertEquals(3, Log::count());

        $this->travel($delete_after)->days();
        $this->artisan('8finity:delete-old-logs');
        $this->assertEquals(0, Log::count());
    }
}
