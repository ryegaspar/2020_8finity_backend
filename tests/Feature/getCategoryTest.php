<?php

namespace Tests\Feature;

use App\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class getCategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_get_categories()
    {
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

        $this->getJson('categories')
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
