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
            $table->string('description');
            $table->string('icon');
            $table->timestamps();
        });

        DB::table('categories')
            ->insert([
                [
                    'type'        => 'in',
                    'description' => 'sales',
                    'icon'        => 'piggy-bank'
                ],
                [
                    'type'        => 'in',
                    'description' => 'carry over',
                    'icon'        => 'money-bill-wave'
                ],
                [
                    'type'        => 'out',
                    'description' => 'cash advance',
                    'icon'        => 'cc-visa',
                ],
                [
                    'type'        => 'out',
                    'description' => 'employee salary',
                    'icon'        => 'credit-card'
                ],
                [
                    'type'        => 'out',
                    'description' => 'electric bill',
                    'icon'        => 'lightbulb'
                ],
                [
                    'type'        => 'out',
                    'description' => 'internet',
                    'icon'        => 'wifi'
                ],
                [
                    'type'        => 'out',
                    'description' => 'water',
                    'icon'        => 'water'
                ],
                [
                    'type'        => 'out',
                    'description' => 'fuel',
                    'icon'        => 'gas-pump'
                ],
                [
                    'type'        => 'out',
                    'description' => 'gas',
                    'icon'        => 'fire'
                ],
                [
                    'type'        => 'out',
                    'description' => 'food',
                    'icon'        => 'utensils'
                ],
                [
                    'type'        => 'out',
                    'description' => 'entertainment',
                    'icon'        => 'tv'
                ],
                [
                    'type'        => 'out',
                    'description' => 'grocery',
                    'icon'        => 'shopping-basket'
                ],
                [
                    'type'        => 'out',
                    'description' => 'equipment/tools',
                    'icon'        => 'tools',
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
