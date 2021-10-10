<?php

namespace Tests\Feature\Admin\Invitation;

use App\Facades\InvitationCode;
use App\Mail\InvitationEmail;
use App\Models\Admin;
use App\Models\Invitation;
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

    /** @test */
    public function guests_cannot_invite()
    {
        $this->json('post', 'admin/invitations', ['email' => 'john@example.com'])
            ->assertStatus(401);
    }

    /** @test */
    public function inviting_an_admin()
    {
        Mail::fake();
        InvitationCode::shouldReceive('generate')->andReturn('TESTCODE1234');

        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('post', 'admin/invitations', ['email' => 'john@example.com'])
            ->assertStatus(201);

        $this->assertEquals(1, Invitation::count());

        $invitation = Invitation::first();
        $this->assertEquals('john@example.com', $invitation->email);
        $this->assertEquals('TESTCODE1234', $invitation->code);

        Mail::assertSent(InvitationEmail::class, function ($mail) use ($invitation) {
            return $mail->hasTo('john@example.com') &&
                $mail->invitation->is($invitation);
        });
    }

    /** @test */
    public function email_is_required()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('post', 'admin/invitations', ['email' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    /** @test */
    public function must_be_a_valid_email()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('post', 'admin/invitations', ['email' => 'john'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }
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
