<?php

namespace Tests\Feature\Admin\Accounting\Categories;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetCategoryTest extends TestCase
{
    use RefreshDatabase;

    private $defaultValues = [
        [
            'id'   => 1,
            'type' => 'income',
            'name' => 'sales',
            'icon' => 'piggy-bank',
        ],
        [
            'id'   => 2,
            'type' => 'income',
            'name' => 'carry over',
            'icon' => 'money-bill-wave',
        ],
        [
            'id'   => 3,
            'type' => 'income',
            'name' => 'payment',
            'icon' => 'money-check-alt'
        ],
        [
            'id'   => 4,
            'type' => 'expense',
            'name' => 'cash advance',
            'icon' => 'coins',
        ],
        [
            'id'   => 5,
            'type' => 'expense',
            'name' => 'employee salary',
            'icon' => 'credit-card',
        ],
        [
            'id'   => 6,
            'type' => 'expense',
            'name' => 'electric bill',
            'icon' => 'lightbulb',
        ],
        [
            'id'   => 7,
            'type' => 'expense',
            'name' => 'internet',
            'icon' => 'wifi',
        ],
        [
            'id'   => 8,
            'type' => 'expense',
            'name' => 'water',
            'icon' => 'water',

        ],
        [
            'id'   => 9,
            'type' => 'expense',
            'name' => 'fuel',
            'icon' => 'gas-pump',
        ],
        [
            'id'   => 10,
            'type' => 'expense',
            'name' => 'gas',
            'icon' => 'fire',
        ],
        [
            'id'   => 11,
            'type' => 'expense',
            'name' => 'food',
            'icon' => 'utensils',
        ],
        [
            'id'   => 12,
            'type' => 'expense',
            'name' => 'entertainment',
            'icon' => 'tv',
        ],
        [
            'id'   => 13,
            'type' => 'expense',
            'name' => 'grocery',
            'icon' => 'shopping-basket',
        ],
        [
            'id'   => 14,
            'type' => 'expense',
            'name' => 'equipment/tools',
            'icon' => 'tools',
        ]
    ];

    /** @test */
    public function only_authenticated_users_can_view_categories()
    {
        $this->withoutExceptionHandling();

        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get('admin/accounting/categories')
            ->assertStatus(200);
    }

    /** @test */
    public function guests_cannot_view_categories()
    {
        $this->withHeaders(['accept' => 'application/json'])
            ->get('admin/accounting/categories')
            ->assertStatus(401);
    }

    /** @test */
    public function can_get_all_categories()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get('admin/accounting/categories/?all')
            ->assertStatus(200)
            ->assertExactJson([
                'data' => $this->defaultValues
            ]);
    }

    /** @test */
    public function can_view_categories_and_is_ordered_by_id_by_default()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get('admin/accounting/categories')
            ->assertJson([
                'data' => $this->defaultValues
            ]);
    }

    /** @test */
    public function has_paginated_data()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get('admin/accounting/categories')
            ->assertJsonStructure([
                'meta' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                    'from',
                    'to',
                ],
                'data',
            ]);
    }

    /** @test */
    public function can_sorted_by_name()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get('admin/accounting/categories?sort=name|asc')
            ->assertJson([
                'data' => [
                    [
                        'id'   => 2,
                        'name' => "carry over",
                    ],
                    [
                        'id'   => 4,
                        'name' => "cash advance",
                    ],
                    [
                        'id'   => 6,
                        'name' => 'electric bill'
                    ],
                    [
                        'id'   => 5,
                        'name' => "employee salary",
                    ],
                ]
            ]);
    }
}
