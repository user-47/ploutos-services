<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentProviderUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_provider_user', function (Blueprint $table) {
            $table->id();
            $table->integer('payment_provider_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('identifier');
            $table->timestamps();

            $table->unique(['payment_provider_id', 'user_id']);

            $table->foreign('payment_provider_id')->references('id')->on('payment_providers');
            $table->foreign('user_id')->references('id')->on('users');

            $table->index(['payment_provider_id', 'identifier']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_provider_user');
    }
}
