<?php

namespace Tests\Unit\Mail;

use App\Mail\InvitationEmail;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function email_contains_a_link_to_accept_the_invitation()
    {
        $invitation = Invitation::factory()->make([
            'email' => 'john@example.com',
            'code' => 'TESTCODE1234'
        ]);

        $email = new InvitationEmail($invitation);

        $this->assertStringContainsString(url('/admin/invitations/TESTCODE1234'), $email->render());
    }

    /** @test */
    public function email_has_the_correct_subject()
    {
        $invitation = Invitation::factory()->make();

        $email = new InvitationEmail($invitation);

        $this->assertEquals(config('app.name'). " Admin Invitation", $email->build()->subject);
    }

}
