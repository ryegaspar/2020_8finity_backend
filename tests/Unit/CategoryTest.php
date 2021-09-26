<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Log;
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
            'id'         => '1',
            'type'       => 'in',
            'name'       => 'sales',
            'icon'       => 'piggy-bank',
            'created_at' => null,
            'updated_at' => null
        ];

        $category2 = [
            'id'         => '2',
            'type'       => 'in',
            'name'       => 'carry over',
            'icon'       => 'money-bill-wave',
            'created_at' => null,
            'updated_at' => null
        ];

        $category3 = [
            'id'         => '3',
            'type'       => 'out',
            'name'       => 'cash advance',
            'icon'       => 'coins',
            'created_at' => null,
            'updated_at' => null
        ];

        $category4 = [
            'id'         => '4',
            'type'       => 'out',
            'name'       => 'employee salary',
            'icon'       => 'credit-card',
            'created_at' => null,
            'updated_at' => null
        ];

        $category5 = [
            'id'         => '5',
            'type'       => 'out',
            'name'       => 'electric bill',
            'icon'       => 'lightbulb',
            'created_at' => null,
            'updated_at' => null
        ];

        $category6 = [
            'id'         => '6',
            'type'       => 'out',
            'name'       => 'internet',
            'icon'       => 'wifi',
            'created_at' => null,
            'updated_at' => null
        ];

        $category7 = [
            'id'         => '7',
            'type'       => 'out',
            'name'       => 'water',
            'icon'       => 'water',
            'created_at' => null,
            'updated_at' => null

        ];

        $category8 = [
            'id'         => '8',
            'type'       => 'out',
            'name'       => 'fuel',
            'icon'       => 'gas-pump',
            'created_at' => null,
            'updated_at' => null
        ];

        $category9 = [
            'id'         => '9',
            'type'       => 'out',
            'name'       => 'gas',
            'icon'       => 'fire',
            'created_at' => null,
            'updated_at' => null
        ];

        $category10 = [
            'id'         => '10',
            'type'       => 'out',
            'name'       => 'food',
            'icon'       => 'utensils',
            'created_at' => null,
            'updated_at' => null
        ];

        $category11 = [
            'id'         => '11',
            'type'       => 'out',
            'name'       => 'entertainment',
            'icon'       => 'tv',
            'created_at' => null,
            'updated_at' => null
        ];

        $category12 = [
            'id'         => '12',
            'type'       => 'out',
            'name'       => 'grocery',
            'icon'       => 'shopping-basket',
            'created_at' => null,
            'updated_at' => null
        ];

        $category13 = [
            'id'         => '13',
            'type'       => 'out',
            'name'       => 'equipment/tools',
            'icon'       => 'tools',
            'created_at' => null,
            'updated_at' => null
        ];


        $this->assertEquals(count($categories), 13);
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
        $this->assertEquals($categories[11], $category12);
        $this->assertEquals($categories[12], $category13);
    }

    /** @test */
    public function log_is_created_when_a_category_gets_added()
    {
        $category = Category::factory()->create();

        $this->assertDatabaseCount('logs', 1);

        tap(Log::first(), function ($log) use ($category) {
            $this->assertEquals('created', $log->action);
            $this->assertEquals('Category', $log->loggable_type);

            $changes = json_decode($log->changes, true);

            $this->assertEquals([
                'name' => $category->name,
                'type' => 'in',
                'icon' => 'fa fa-money-bill'
            ], $changes);
        });
    }

    /** @test */
    public function log_is_created_when_a_category_gets_updated()
    {
        $category = Category::factory()->create([
            'name' => 'old',
            'type' => 'in',
            'icon' => 'old icon'
        ]);

        $category->update([
            'name' => 'new',
            'type' => 'out',
            'icon' => 'new icon'
        ]);

        $this->assertDatabaseCount('logs', 2);

        tap(Log::find(2), function ($log) {
            $this->assertEquals('updated', $log->action);
            $this->assertEquals('Category', $log->loggable_type);

            $changes = json_decode($log->changes, true);

            $this->assertEquals([
                'before' => [
                    'name' => 'old',
                    'type' => 'in',
                    'icon' => 'old icon',
                ],
                'after'  => [
                    'name' => 'new',
                    'type' => 'out',
                    'icon' => 'new icon',
                ]
            ], $changes);
        });
    }

    /** @test */
    public function log_is_created_when_a_category_gets_deleted()
    {
        $category = Category::factory()->create();

        $category->delete();

        $this->assertDatabaseCount('logs', 2);

        tap(Log::find(2), function ($log) use ($category) {
            $this->assertEquals('deleted', $log->action);
            $this->assertEquals('Category', $log->loggable_type);

            $changes = json_decode($log->changes, true);

            $this->assertEquals([
                'name' => $category->name,
                'type' => $category->type,
                'icon' => $category->icon,
            ], $changes);
        });
    }
}
