<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->integer('user_id');
            $table->string('provider_id')->nullable();
            $table->string('brand'); //Visa, Mastercard, etc
            $table->string('last_four');
            $table->integer('exp_month');
            $table->integer('exp_year');
            $table->string('fingerprint');
            $table->string('name')->nullable();
            $table->string('tokenization_method')->nullable(); //apple pay or android pay
            $table->integer('payment_provider_id')->nullable()->unsigned();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
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
        Schema::dropIfExists('cards');
    }
}
