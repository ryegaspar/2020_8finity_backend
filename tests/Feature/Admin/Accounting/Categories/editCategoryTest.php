<?php

namespace Tests\Feature\Admin\Accounting\Categories;

use App\Models\Admin;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class editCategoryTest extends TestCase
{
    use RefreshDatabase;

    private function oldCategory($overrides = [])
    {
        return array_merge([
            'type' => 'in',
            'name' => 'old in category',
            'icon' => 'piggy-bank'
        ], $overrides);
    }

    private function newCategory($overrides = [])
    {
        return array_merge([
            'type' => 'in',
            'name' => 'new in transaction',
            'icon' => 'money-bill-wave'
        ], $overrides);
    }

    /** @test */
    public function only_authenticated_users_can_update_categories()
    {
        $admin = Admin::factory()->create();

        $category = Category::factory()->create($this->oldCategory());

        $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/categories/{$category->id}", $this->newCategory())
            ->assertStatus(204);
    }

    /** @test */
    public function guests_cannot_update_categories()
    {
        $category = Category::factory()->create($this->oldCategory());

        $this->json('patch', "admin/accounting/categories/{$category->id}", $this->newCategory())
            ->assertStatus(401);
    }

    /** @test */
    public function updating_a_category()
    {
        $admin = Admin::factory()->create();
        $category = Category::factory()->create($this->oldCategory());

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/categories/{$category->id}", $this->newCategory());

        tap(Category::latest()->first(), function ($category) use ($response, $admin) {
            $response->assertStatus(204);

            $this->assertEquals('in', $category->type);
            $this->assertEquals('new in transaction', $category->name);
            $this->assertEquals('money-bill-wave', $category->icon);
        });
    }

    /** @test */
    public function cannot_update_default_categories_ids_upto_13()
    {
        $admin = Admin::factory()->create();

        $category = Category::find(1);

        $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/categories/{$category->id}", $this->newCategory())
            ->assertStatus(422);
    }

    /** @test */
    public function type_is_required()
    {
        $admin = Admin::factory()->create();
        $category = Category::factory()->create($this->oldCategory());

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/categories/{$category->id}", $this->newCategory([
                'type' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('type');
    }

    /** @test */
    public function name_is_required()
    {
        $admin = Admin::factory()->create();
        $category = Category::factory()->create($this->oldCategory());

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/categories/{$category->id}", $this->newCategory([
                'name' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    /** @test */
    public function name_must_be_unique()
    {
        $admin = Admin::factory()->create();

        $category = Category::factory()->create($this->oldCategory());

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/categories/{$category->id}", $this->newCategory([
                'name' => 'sales'
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    /** @test */
    public function can_be_updated_when_icon_or_type_are_only_changed()
    {
        $admin = Admin::factory()->create();

        $category = Category::factory()->create([
            'type' => 'in',
            'name' => 'new category',
            'icon' => 'piggy-bank'
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/categories/{$category->id}", [
                'type' => 'out',
                'name' => 'new category',
                'icon' => 'money-bill-wave'
            ]);

        tap(Category::latest()->first(), function ($category) use ($response, $admin) {
            $response->assertStatus(204);

            $this->assertEquals('out', $category->type);
            $this->assertEquals('new category', $category->name);
            $this->assertEquals('money-bill-wave', $category->icon);
        });
    }

    /** @test */
    public function icon_is_required()
    {
        $admin = Admin::factory()->create();
        $category = Category::factory()->create($this->oldCategory());

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/categories/{$category->id}", $this->newCategory([
                'icon' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('icon');
    }

    /** @test */
    public function type_must_only_be_in_or_out()
    {
        $admin = Admin::factory()->create();
        $category = Category::factory()->create($this->oldCategory());

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/categories/{$category->id}", $this->newCategory([
                'type' => 'other'
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('type');

    }
}
