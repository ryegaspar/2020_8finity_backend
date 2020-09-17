<?php

namespace Tests\Feature;

use App\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class getCategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_view_categories()
    {
        $admin = factory(Admin::class)->create();

        $this->actingAs($admin, 'admin')
            ->get('/categories')
            ->assertStatus(200);
    }

    /** @test */
    public function guests_cannot_view_categories()
    {
        $this->withHeaders(['accept' => 'application/json'])
            ->get('/categories')
            ->assertStatus(401);
    }

    /** @test */
    public function can_get_categories()
    {
        $admin = factory(Admin::class)->create();

        $this->actingAs($admin, 'admin')
            ->withHeaders(['accept' => 'application/json'])
            ->getJson('categories')
            ->assertStatus(200)
            ->assertExactJson([
                'data' => [
                    [
                        'id'          => 1,
                        'type'        => 'income',
                        'description' => 'sales',
                        'icon'        => 'piggy-bank',
                    ],
                    [
                        'id'          => 2,
                        'type'        => 'income',
                        'description' => 'carry over',
                        'icon'        => 'money-bill-wave',
                    ],
                    [
                        'id'          => 3,
                        'type'        => 'expense',
                        'description' => 'employee salary',
                        'icon'        => 'credit-card',
                    ],
                    [
                        'id'          => 4,
                        'type'        => 'expense',
                        'description' => 'electric bill',
                        'icon'        => 'lightbulb',
                    ],
                    [
                        'id'          => 5,
                        'type'        => 'expense',
                        'description' => 'internet',
                        'icon'        => 'wifi',
                    ],
                    [
                        'id'          => 6,
                        'type'        => 'expense',
                        'description' => 'water',
                        'icon'        => 'water',

                    ],
                    [
                        'id'          => 7,
                        'type'        => 'expense',
                        'description' => 'fuel',
                        'icon'        => 'gas-pump',
                    ],
                    [
                        'id'          => 8,
                        'type'        => 'expense',
                        'description' => 'gas',
                        'icon'        => 'fire',
                    ],
                    [
                        'id'          => 9,
                        'type'        => 'expense',
                        'description' => 'food',
                        'icon'        => 'utensils',
                    ],
                    [
                        'id'          => 10,
                        'type'        => 'expense',
                        'description' => 'entertainment',
                        'icon'        => 'tv',
                    ],
                    [
                        'id'          => 11,
                        'type'        => 'expense',
                        'description' => 'grocery',
                        'icon'        => 'shopping-basket',
                    ]
                ]
            ]);
    }
}
