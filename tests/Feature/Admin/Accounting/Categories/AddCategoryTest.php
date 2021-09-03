<?php

namespace Tests\Feature\Admin\Accounting\Categories;

use App\Models\Admin;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddCategoryTest extends TestCase
{
    use RefreshDatabase;

    private function validParams($overrides = [])
    {
        return array_merge([
            'type' => 'in',
            'name' => 'extra income',
            'icon' => 'money-bill',
        ], $overrides);
    }

    /** @test */
    public function only_authenticated_users_can_add_categories()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/categories', $this->validParams())
            ->assertStatus(201);
    }

    /** @test */
    public function guests_cannot_add_categories()
    {
        $this->json('post', 'admin/accounting/categories', $this->validParams())
            ->assertStatus(401);
    }

    /** @test */
    public function adding_a_category()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/categories', $this->validParams());

        tap(Category::latest()->first(), function ($category) use ($response, $admin) {
            $response->assertStatus(201);

            $this->assertEquals('in', $category->type);
            $this->assertEquals('extra income', $category->name);
            $this->assertEquals('money-bill', $category->icon);
        });
    }

    /** @test */
    public function type_is_required()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/categories', $this->validParams([
                'type' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('type');
    }

    /** @test */
    public function type_can_only_be_in_or_out()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/categories', $this->validParams([
                'type' => 'not a valid type'
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('type');
    }

    /** @test */
    public function name_is_required()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/categories', $this->validParams([
                'name' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    /** @test */
    public function name_must_be_unique()
    {
        $admin = Admin::factory()->create();

        Category::factory()->create([
            'name' => 'new category'
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/categories', $this->validParams([
                'name' => 'new category'
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    /** @test */
    public function icon_is_required()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/categories', $this->validParams([
                'icon' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('icon');
    }

    //TODO: can only accept specific icons
    /* insert code here*/
}
