<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['in', 'out'])->default('in');
            $table->string('name');
            $table->string('icon');
            $table->timestamps();
        });

        DB::table('categories')
            ->insert([
                [
                    'type' => 'in',
                    'name' => 'sales',
                    'icon' => 'piggy-bank'
                ],
                [
                    'type' => 'in',
                    'name' => 'carry over',
                    'icon' => 'money-bill-wave'
                ],
                [
                    'type' => 'out',
                    'name' => 'cash advance',
                    'icon' => 'coins',
                ],
                [
                    'type' => 'out',
                    'name' => 'employee salary',
                    'icon' => 'credit-card'
                ],
                [
                    'type' => 'out',
                    'name' => 'electric bill',
                    'icon' => 'lightbulb'
                ],
                [
                    'type' => 'out',
                    'name' => 'internet',
                    'icon' => 'wifi'
                ],
                [
                    'type' => 'out',
                    'name' => 'water',
                    'icon' => 'water'
                ],
                [
                    'type' => 'out',
                    'name' => 'fuel',
                    'icon' => 'gas-pump'
                ],
                [
                    'type' => 'out',
                    'name' => 'gas',
                    'icon' => 'fire'
                ],
                [
                    'type' => 'out',
                    'name' => 'food',
                    'icon' => 'utensils'
                ],
                [
                    'type' => 'out',
                    'name' => 'entertainment',
                    'icon' => 'tv'
                ],
                [
                    'type' => 'out',
                    'name' => 'grocery',
                    'icon' => 'shopping-basket'
                ],
                [
                    'type' => 'out',
                    'name' => 'equipment/tools',
                    'icon' => 'tools',
                ]
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
}
