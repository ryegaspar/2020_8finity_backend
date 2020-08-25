<?php

namespace Tests\Feature;

use App\Category;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class getCategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_view_categories()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user)
            ->get('/categories')
            ->assertStatus(200);
    }

    /** @test */
    public function guests_cannot_view_categories()
    {
        $this->get('/categories')
            ->assertStatus(302);
    }

    /** @test */
    public function can_get_categories()
    {
        $user = factory(User::class)->create();

        $category1 = Category::create([
            'type'        => 'in',
            'description' => 'Salary',
            'icon'        => 'fa fa-money-bill'
        ]);

        $category2 = Category::create([
            'type'        => 'out',
            'description' => 'Shopping',
            'icon'        => 'fa fa-shopping-cart'
        ]);

        $this->actingAs($user)
            ->getJson('categories')
            ->assertExactJson([
                'data' => [
                    [
                        'id'          => $category1->id,
                        'type'        => 'income',
                        'description' => 'Salary',
                        'icon'        => 'fa fa-money-bill'
                    ],
                    [
                        'id'          => $category2->id,
                        'type'        => 'expense',
                        'description' => 'Shopping',
                        'icon'        => 'fa fa-shopping-cart'
                    ]
                ]
            ]);
    }
}
