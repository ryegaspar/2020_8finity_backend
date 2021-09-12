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
            $table->boolean('is_cancelled')->default(false);
            $table->text('notes')->nullable();
            $table->date('post_date');
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
