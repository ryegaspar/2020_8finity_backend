<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        Schema::create('transfers', function (Blueprint $table) {
//            $table->id();
//            $table->foreignId('from_account')->constrained('accounts', 'account_id');
//            $table->foreignId('to_account')->constrained('accounts', 'account_id');
//            $table->foreignId('admin_id')->constrained();
//            $table->BigInteger('amount');
//            $table->string('description', 255)->nullable();
//            $table->text('notes')->nullable();
//            $table->date('date');
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::dropIfExists('transfers');
    }
}