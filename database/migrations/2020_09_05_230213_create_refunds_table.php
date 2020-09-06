<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('provider_id')->nullable();
            $table->string('charge_id')->nullable();
            $table->integer('invoice_id')->unsigned()->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->string('currency');
            $table->integer('amount');
            $table->integer('amount_left')->default(0);
            $table->enum('customer_reason', ['duplicate', 'fraudulent', 'requested_by_customer', 'other'])->nullable();
            $table->text('internal_reason')->nullable();
            $table->enum('status', ['pending', 'succeeded', 'failed'])->default('pending');
            $table->integer('admin_id')->unsigned()->nullable();
            $table->integer('payment_provider_id')->nullable()->unsigned();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');
            $table->foreign('payment_provider_id')->references('id')->on('payment_providers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('refunds');
    }
}
