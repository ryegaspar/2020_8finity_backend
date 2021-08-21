<?php

namespace Tests\Feature\Admin\Categories;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class getCategoryTest extends TestCase
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
            'type' => 'expense',
            'name' => 'cash advance',
            'icon' => 'coins',
        ],
        [
            'id'   => 4,
            'type' => 'expense',
            'name' => 'employee salary',
            'icon' => 'credit-card',
        ],
        [
            'id'   => 5,
            'type' => 'expense',
            'name' => 'electric bill',
            'icon' => 'lightbulb',
        ],
        [
            'id'   => 6,
            'type' => 'expense',
            'name' => 'internet',
            'icon' => 'wifi',
        ],
        [
            'id'   => 7,
            'type' => 'expense',
            'name' => 'water',
            'icon' => 'water',

        ],
        [
            'id'   => 8,
            'type' => 'expense',
            'name' => 'fuel',
            'icon' => 'gas-pump',
        ],
        [
            'id'   => 9,
            'type' => 'expense',
            'name' => 'gas',
            'icon' => 'fire',
        ],
        [
            'id'   => 10,
            'type' => 'expense',
            'name' => 'food',
            'icon' => 'utensils',
        ],
        [
            'id'   => 11,
            'type' => 'expense',
            'name' => 'entertainment',
            'icon' => 'tv',
        ],
        [
            'id'   => 12,
            'type' => 'expense',
            'name' => 'grocery',
            'icon' => 'shopping-basket',
        ],
        [
            'id'   => 13,
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
            ->get('admin/categories')
            ->assertStatus(200);
    }

    /** @test */
    public function guests_cannot_view_categories()
    {
        $this->withHeaders(['accept' => 'application/json'])
            ->get('admin/categories')
            ->assertStatus(401);
    }

    /** @test */
    public function can_get_all_categories()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->withHeaders(['accept' => 'application/json'])
            ->getJson('admin/categories/?all')
            ->assertStatus(200)
            ->assertExactJson([
                'data' => $this->defaultValues
            ]);
    }

    /** @test */
    public function can_view_categories_and_is_ordered_by_id()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->withHeaders(['accept' => 'application/json'])
            ->getJson('admin/categories')
            ->assertJson([
                'data' => $this->defaultValues
            ]);
    }

    /** @test */
    public function has_paginated_data()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->withHeaders(['accept' => 'application/json'])
            ->getJson('admin/categories')
            ->assertJsonStructure([
                'total',
                'per_page',
                'current_page',
                'last_page',
                'next_page_url',
                'prev_page_url',
                'from',
                'to',
                'data',
            ]);
    }

    /** @test */
    public function can_sorted_by_name()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->withHeaders(['accept' => 'application/json'])
            ->getJson('admin/categories?sort=name|asc')
            ->assertJson([
                'data' => [
                    [
                        'id'     => 2,
                        'name' => "carry over",
                    ],
                    [
                        'id'     => 3,
                        'name' => "cash advance",
                    ],
                    [
                        'id' => 5,
                        'name' => 'electric bill'
                    ],
                    [
                        'id'     => 4,
                        'name' => "employee salary",
                    ],
                ]
            ]);
    }
}
