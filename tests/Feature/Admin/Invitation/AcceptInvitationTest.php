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

    private function validParams($overrides = [])
    {
        return array_merge([
            'first_name'            => 'john',
            'last_name'             => 'doe',
            'username'              => 'johndoe',
            'email'                 => 'john@example.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'invitation_code'       => 'TEST1234'
        ], $overrides);
    }

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

        $response = $this->json('post', 'admin/register', $this->validParams())->assertStatus(201);

        $this->assertEquals(1, Admin::count());
        $admin = Admin::first();

        $this->assertEquals('john@example.com', $admin->email);
        $this->assertTrue(Hash::check('secret123', $admin->password));
        $this->assertTrue($invitation->fresh()->user->is($admin));
    }

    /** @test */
    public function registering_with_a_used_invitation_code()
    {
        Invitation::factory()->create([
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

    /** @test */
    public function first_name_is_required()
    {
        Invitation::factory()->create([
            'admin_id' => null,
            'code'     => 'TEST1234',
        ]);

        $this->json('post', 'admin/register', $this->validParams([
            'first_name' => ''
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('first_name');
    }

    /** @test */
    public function last_name_is_required()
    {
        Invitation::factory()->create([
            'admin_id' => null,
            'code'     => 'TEST1234',
        ]);

        $this->json('post', 'admin/register', $this->validParams([
            'last_name' => ''
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('last_name');
    }

    /** @test */
    public function username_is_required()
    {
        Invitation::factory()->create([
            'admin_id' => null,
            'code'     => 'TEST1234',
        ]);

        $this->json('post', 'admin/register', $this->validParams([
            'username' => ''
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('username');
    }

    /** @test */
    public function must_be_a_valid_username()
    {
        Invitation::factory()->create([
            'admin_id' => null,
            'code'     => 'TEST1234',
        ]);

        $this->json('post', 'admin/register', $this->validParams([
            'username' => 'john doe@'
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('username');
    }

    /** @test */
    public function must_be_a_unique_username()
    {
        Admin::factory()->create([
            'username' => 'janedoe'
        ]);

        Invitation::factory()->create([
            'admin_id' => null,
            'code'     => 'TEST1234',
        ]);

        $this->json('post', 'admin/register', $this->validParams([
            'username' => 'janedoe'
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('username');

    }

    /** @test */
    public function email_is_required()
    {
        Invitation::factory()->create([
            'admin_id' => null,
            'code'     => 'TEST1234',
        ]);

        $this->json('post', 'admin/register', $this->validParams([
            'email' => ''
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    /** @test */
    public function must_be_a_valid_email()
    {
        Invitation::factory()->create([
            'admin_id' => null,
            'code'     => 'TEST1234',
        ]);

        $this->json('post', 'admin/register', $this->validParams([
            'email' => 'invalid-email'
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    /** @test */
    public function password_is_required()
    {
        Invitation::factory()->create([
            'admin_id' => null,
            'code'     => 'TEST1234',
        ]);

        $this->json('post', 'admin/register', $this->validParams([
            'password' => ''
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    /** @test */
    public function minimum_of_8_characters_is_required_for_password()
    {
        Invitation::factory()->create([
            'admin_id' => null,
            'code'     => 'TEST1234',
        ]);

        $this->json('post', 'admin/register', $this->validParams([
            'password' => 'pass'
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    /** @test */
    public function password_confirmation_is_required()
    {
        Invitation::factory()->create([
            'admin_id' => null,
            'code'     => 'TEST1234',
        ]);

        $this->json('post', 'admin/register', $this->validParams([
            'password_confirmation' => ''
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }
}
