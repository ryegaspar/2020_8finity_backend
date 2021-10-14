<?php

namespace Tests\Feature\Admin\Accounting\Checks;

use App\Models\Admin;
use App\Models\Check;
use Database\Factories\DatabaseNotificationFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckDueNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_notification_is_prepared_for_the_admin_when_a_check_is_due()
    {
        $admin = Admin::factory()->create();
        Check::factory()->create(['admin_id' => $admin->id]);

        $this->assertCount(0, $admin->notifications);
        $this->artisan('8finity:notify_check_due');

        $this->assertCount(1, $admin->fresh()->notifications);
    }

    /** @test */
    public function an_admin_can_fetch_their_unread_notifications()
    {
        $admin = Admin::factory()->create();

        DatabaseNotificationFactory::new()->create([
            'notifiable_id' => $admin->id,
            'data'          => [
                'message' => 'message'
            ]
        ]);

        $this->actingAs($admin, 'admin')
            ->json('get', 'admin/notifications')
            ->assertJsonStructure([
                'data' => [
                    [
                        "created_at",
                        "message"
                    ]
                ]
            ]);
    }

    /** @test */
    public function an_admin_can_mark_all_notifications_as_read()
    {
        $this->withoutExceptionHandling();
        $admin = Admin::factory()->create();

        DatabaseNotificationFactory::new()->create(['notifiable_id' => $admin->id]);
        DatabaseNotificationFactory::new()->create(['notifiable_id' => $admin->id]);

        $this->assertCount(2, $admin->unreadNotifications);

        $this->actingAs($admin, 'admin')
            ->json('delete', 'admin/notifications');

        $this->assertCount(0, $admin->fresh()->unreadNotifications);
    }
}
