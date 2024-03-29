<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChecksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained();
            $table->foreignId('account_id')->constrained();
            $table->foreignId('admin_id')->constrained();
            $table->foreignId('transaction_id')->nullable();
            $table->BigInteger('amount');
            $table->string('description', 255)->nullable();
            $table->enum('status', ['pending', 'cleared', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->date('due_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('checks');
    }
}
