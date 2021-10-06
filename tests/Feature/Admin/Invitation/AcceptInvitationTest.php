<?php

namespace Tests\Feature\Admin\Invitation;

use App\Models\Admin;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AcceptInvitationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function getting_an_unused_invitation()
    {
        Invitation::factory()->create([
            'admin_id' => null,
            'code'     => 'TEST1234'
        ]);

        $this->get('/admin/invitations/TEST1234')
            ->assertStatus(200)
            ->assertExactJson([
                'id'   => 1,
                'code' => 'TEST1234'
            ]);

    }

    /** @test */
    public function using_a_used_invitation()
    {
        Invitation::factory()->create([
            'admin_id' => Admin::factory()->create(),
            'code'     => 'TEST1234'
        ]);

        $this->get('/admin/invitations/TEST1234')
            ->assertStatus(404);

    }

    /** @test */
    public function getting_an_invitation_that_does_not_exists()
    {
        $this->get('/admin/invitations/TEST1234')
            ->assertStatus(404);
    }

    /** @test */
    public function registering_with_a_valid_invitation_code()
    {
        $invitation = Invitation::factory()->create([
            'admin_id' => null,
            'code'     => 'TEST1234',
        ]);

        $response = $this->json('post', 'admin/register', [
            'first_name'            => 'john',
            'last_name'             => 'doe',
            'username'              => 'johndoe',
            'email'                 => 'john@example.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'invitation_code'       => 'TEST1234'
        ])->assertStatus(201);

        $this->assertEquals(1, Admin::count());
        $admin = Admin::first();

        $this->assertEquals('john@example.com', $admin->email);
        $this->assertTrue(Hash::check('secret123', $admin->password));
        $this->assertTrue($invitation->fresh()->user->is($admin));
    }

    /** @test */
    public function registering_with_a_used_invitation_code()
    {
        $invitation = Invitation::factory()->create([
            'admin_id' => Admin::factory()->create(),
            'code'     => 'TEST1234',
        ]);

        $this->assertEquals(1, Admin::count());

        $this->json('post', 'admin/register', [
            'first_name'            => 'john',
            'last_name'             => 'doe',
            'username'              => 'johndoe',
            'email'                 => 'john@example.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'invitation_code'       => 'TEST1234'
        ])->assertStatus(404);

        $this->assertEquals(1, Admin::count());
    }

    /** @test */
    public function registering_with_a_non_existent_invitation_code()
    {
        $this->json('post', 'admin/register', [
            'first_name'            => 'john',
            'last_name'             => 'doe',
            'username'              => 'johndoe',
            'email'                 => 'john@example.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'invitation_code'       => 'TEST1234'
        ])->assertStatus(404);

        $this->assertEquals(0, Admin::count());
    }
//    /** @test */
//    public function guests_cannot_invite()
//    {
//        $this->json('post', 'admin/invite', ['email' => 'john@example.com'])
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
