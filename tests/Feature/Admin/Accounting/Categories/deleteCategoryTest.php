<?php

namespace Tests\Feature\Admin\Accounting\Categories;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class deleteCategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_delete_category()
    {
        $admin = Admin::factory()->create();
        $this->withoutExceptionHandling();

        $category = Category::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/categories/{$category->id}")
            ->assertStatus(204);
    }

    /** @test */
    public function guests_cannot_delete_transaction()
    {
        $category = Category::factory()->create();

        $this->json('delete', "admin/accounting/categories/{$category->id}")
            ->assertStatus(401);
    }

    /** @test */
    public function cannot_delete_default_categories()
    {
        $admin = Admin::factory()->create();

        $category = Category::find(1);

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/categories/{$category->id}")
            ->assertStatus(422);
    }

    /** @test */
    public function deleting_a_transaction()
    {
        $admin = Admin::factory()->create();

        $category = Category::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/categories/{$category->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);

        $this->assertEquals(13, Category::count());
    }

    /** @test */
    public function cannot_delete_if_category_has_transaction()
    {
        $admin = Admin::factory()->create();

        $category = Category::factory()->create();

        $transaction = Transaction::factory()->create([
            'category_id' => $category->id,
            'admin_id'    => $admin->id
        ]);

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/categories/{$category->id}")
            ->assertStatus(409);

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }
}
