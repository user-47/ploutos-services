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
            $table->string('uuid')->unique();
            $table->foreignId('trade_id');
            $table->foreignId('seller_id');
            $table->foreignId('buyer_id');
            $table->integer('amount');
            $table->string('currency');
            $table->enum('type', ['buy', 'sell']);
            $table->enum('status', ['open', 'accepted', 'paid', 'rejected', 'cancelled'])->default('open');
            $table->timestamps();
            $table->softDeletes();
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
