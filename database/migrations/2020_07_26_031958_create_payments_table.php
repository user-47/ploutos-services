<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('reference_no')->unique();
            $table->foreignId('user_id');
            $table->foreignId('transaction_id');
            $table->integer('amount');
            $table->string('currency');
            $table->string('provider')->nullable();
            $table->enum('status', ['draft', 'paid', 'failed', 'cancelled', 'refunded'])->default('draft');
            $table->dateTime('paid_at')->nullable();
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
        Schema::dropIfExists('payments');
    }
}
