<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetAdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_view_admin_lists()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get('admin/lists')
            ->assertStatus(200);
    }

    /** @test */
    public function guests_cannot_view_admin_lists()
    {
        $this->withHeaders(['accept' => 'application/json'])
            ->get('admin/lists')
            ->assertStatus(401);
    }

    /** @test */
    public function can_get_all_admins()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get('admin/lists')
            ->assertStatus(200)
            ->assertExactJson([
                'data' => [
                    [
                        'id'        => $admin->id,
                        'full_name' => $admin->fullName,
                        'is_active' => true,
                        'username'  => $admin->username
                    ]
                ]
            ]);
    }
}
