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
    public function inviting_an_admin_via_cli()
    {
        Mail::fake();
        InvitationCode::shouldReceive('generate')->andReturn('TESTCODE1234');

        $this->artisan('8finity:invite-admin', ['email' => 'john@example.com']);

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
    public function an_invitation_is_resent_if_the_email_invitation_has_not_yet_accepted()
    {
        Mail::fake();

        $invitation = Invitation::factory()->create([
            'code'  => 'TESTCODE1234',
            'email' => 'john@example.com'
        ]);

        $this->assertEquals(1, Invitation::count());

        $this->artisan('8finity:invite-admin', ['email' => 'john@example.com']);

        $this->assertEquals(1, Invitation::count());

        Mail::assertSent(InvitationEmail::class, function ($mail) use ($invitation) {
            return $mail->hasTo('john@example.com') &&
                $mail->invitation->is($invitation);
        });
    }

    /** @test */
    public function an_invitation_is_not_sent_if_the_email_is_a_registered_admin()
    {
        Mail::fake();

        Invitation::factory()->create([
            'code'     => 'TESTCODE1234',
            'email'    => 'john@example.com',
            'admin_id' => Admin::factory()->create()
        ]);

        $this->assertEquals(1, Invitation::count());

        $this->artisan('8finity:invite-admin', ['email' => 'john@example.com'])
            ->expectsOutput('that email is already a registered admin');

        $this->assertEquals(1, Invitation::count());

        Mail::assertNothingSent();
    }
}
