<?php

namespace Tests\Feature\Admin\Accounting\Accounts;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class getAccountTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_view_accounts()
    {
        $this->withoutExceptionHandling();

        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get('admin/accounting/accounts')
            ->assertStatus(200);
    }

//    /** @test */
//    public function guests_cannot_view_categories()
//    {
//        $this->withHeaders(['accept' => 'application/json'])
//            ->get('admin/accounting/categories')
//            ->assertStatus(401);
//    }
//
//    /** @test */
//    public function can_get_all_categories()
//    {
//        $admin = Admin::factory()->create();
//
//        $this->actingAs($admin, 'admin')
//            ->withHeaders(['accept' => 'application/json'])
//            ->getJson('admin/accounting/categories/?all')
//            ->assertStatus(200)
//            ->assertExactJson([
//                'data' => $this->defaultValues
//            ]);
//    }
//
//    /** @test */
//    public function can_view_categories_and_is_ordered_by_id()
//    {
//        $admin = Admin::factory()->create();
//
//        $this->actingAs($admin, 'admin')
//            ->withHeaders(['accept' => 'application/json'])
//            ->getJson('admin/accounting/categories')
//            ->assertJson([
//                'data' => $this->defaultValues
//            ]);
//    }
//
//    /** @test */
//    public function has_paginated_data()
//    {
//        $admin = Admin::factory()->create();
//
//        $this->actingAs($admin, 'admin')
//            ->withHeaders(['accept' => 'application/json'])
//            ->getJson('admin/accounting/categories')
//            ->assertJsonStructure([
//                'total',
//                'per_page',
//                'current_page',
//                'last_page',
//                'next_page_url',
//                'prev_page_url',
//                'from',
//                'to',
//                'data',
//            ]);
//    }
//
//    /** @test */
//    public function can_sorted_by_name()
//    {
//        $admin = Admin::factory()->create();
//
//        $this->actingAs($admin, 'admin')
//            ->withHeaders(['accept' => 'application/json'])
//            ->getJson('admin/accounting/categories?sort=name|asc')
//            ->assertJson([
//                'data' => [
//                    [
//                        'id'     => 2,
//                        'name' => "carry over",
//                    ],
//                    [
//                        'id'     => 3,
//                        'name' => "cash advance",
//                    ],
//                    [
//                        'id' => 5,
//                        'name' => 'electric bill'
//                    ],
//                    [
//                        'id'     => 4,
//                        'name' => "employee salary",
//                    ],
//                ]
//            ]);
//    }
}
