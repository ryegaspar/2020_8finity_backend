<?php

namespace Tests\Unit;

use App\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function category_is_preloaded_with_default_values()
    {
        $categories = Category::all()->toArray();

        $category1 = [
            'id'          => '1',
            'type'        => 'in',
            'description' => 'sales',
            'icon'        => 'piggy-bank',
            'created_at'  => null,
            'updated_at'  => null
        ];

        $category2 = [
            'id'          => '2',
            'type'        => 'in',
            'description' => 'carry over',
            'icon'        => 'money-bill-wave',
            'created_at'  => null,
            'updated_at'  => null
        ];

        $category3 = [
            'id'          => '3',
            'type'        => 'out',
            'description' => 'employee salary',
            'icon'        => 'credit-card',
            'created_at'  => null,
            'updated_at'  => null
        ];

        $category4 = [
            'id'          => '4',
            'type'        => 'out',
            'description' => 'electric bill',
            'icon'        => 'lightbulb',
            'created_at'  => null,
            'updated_at'  => null
        ];

        $category5 = [
            'id'          => '5',
            'type'        => 'out',
            'description' => 'internet',
            'icon'        => 'wifi',
            'created_at'  => null,
            'updated_at'  => null
        ];

        $category6 = [
            'id'          => '6',
            'type'        => 'out',
            'description' => 'water',
            'icon'        => 'water',
            'created_at'  => null,
            'updated_at'  => null

        ];

        $category7 = [
            'id'          => '7',
            'type'        => 'out',
            'description' => 'fuel',
            'icon'        => 'gas-pump',
            'created_at'  => null,
            'updated_at'  => null
        ];

        $category8 = [
            'id'          => '8',
            'type'        => 'out',
            'description' => 'gas',
            'icon'        => 'fire',
            'created_at'  => null,
            'updated_at'  => null
        ];

        $category9 = [
            'id'          => '9',
            'type'        => 'out',
            'description' => 'food',
            'icon'        => 'utensils',
            'created_at'  => null,
            'updated_at'  => null
        ];

        $category10 = [
            'id'          => '10',
            'type'        => 'out',
            'description' => 'entertainment',
            'icon'        => 'tv',
            'created_at'  => null,
            'updated_at'  => null
        ];

        $category11 = [
            'id'          => '11',
            'type'        => 'out',
            'description' => 'grocery',
            'icon'        => 'shopping-basket',
            'created_at'  => null,
            'updated_at'  => null
        ];


        $this->assertEquals(count($categories), 11);
        $this->assertEquals($categories[0], $category1);
        $this->assertEquals($categories[1], $category2);
        $this->assertEquals($categories[2], $category3);
        $this->assertEquals($categories[3], $category4);
        $this->assertEquals($categories[4], $category5);
        $this->assertEquals($categories[5], $category6);
        $this->assertEquals($categories[6], $category7);
        $this->assertEquals($categories[7], $category8);
        $this->assertEquals($categories[8], $category9);
        $this->assertEquals($categories[9], $category10);
        $this->assertEquals($categories[10], $category11);
    }
}
