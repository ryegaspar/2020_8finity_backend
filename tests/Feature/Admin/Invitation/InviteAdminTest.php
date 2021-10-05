<?php

namespace Tests\Feature\Admin\Invitation;

use App\Models\Account;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InviteAdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_invite()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('post', 'admin/invitations', ['email' => 'john@example.com'])
            ->assertStatus(201);
    }

//    /** @test */
//    public function guests_cannot_invite()
//    {
//        $this->json('post', 'admin/invitations', ['email' => 'john@example.com'])
//            ->assertStatus(401);
//    }

//    /** @test */
//    public function inviting_an_admin()
//    {
//        Mail::fake();
//        $admin = Admin::factory()->create();
//
//        $this->actingAs($admin, 'admin')
//            ->json('post', 'admin/invite', ['email' => 'john@example.com'])
//            ->assertStatus(201);
//
//        tap(Account::latest()->first(), function ($account) use ($admin) {
//
//            $this->assertEquals('new', $account->name);
//            $this->assertTrue($account->is_active);
//        });
//    }
//
//    /** @test */
//    public function name_is_required()
//    {
//        $admin = Admin::factory()->create();
//
//        $response = $this->actingAs($admin, 'admin')
//            ->json('post', 'admin/accounting/accounts', ['name' => '']);
//
//        $response->assertStatus(422);
//        $response->assertJsonValidationErrors('name');
//    }
//
//    /** @test */
//    public function name_must_be_unique()
//    {
//        $admin = Admin::factory()->create();
//        Account::factory()->create([
//            'name' => 'new account'
//        ]);
//
//        $response = $this->actingAs($admin, 'admin')
//            ->json('post', 'admin/accounting/accounts', ['name' => 'new account']);
//
//        $response->assertStatus(422);
//        $response->assertJsonValidationErrors('name');
//
//    }
}
