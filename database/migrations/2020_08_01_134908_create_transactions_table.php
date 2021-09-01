<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('admin_id');
            $table->BigInteger('amount');
            $table->string('description', 255)->nullable();
            $table->text('notes')->nullable();
            $table->date('date');
            $table->timestamps();

            $table->foreign('category_id')
                ->references('id')
                ->on('categories');
//                ->cascadeOnDelete();

            $table->foreign('admin_id')
                ->references('id')
                ->on('admins');
//                ->cascadeOnDelete();

            $table->foreign('account_id')
                ->references('id')
                ->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
