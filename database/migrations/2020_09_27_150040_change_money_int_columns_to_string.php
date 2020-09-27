<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeMoneyIntColumnsToString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('amount')->change();
            $table->string('amount_paid')->change();
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->string('amount')->change();
            $table->string('amount_left')->change();
        });

        Schema::table('trades', function (Blueprint $table) {
            $table->string('amount')->change();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('amount')->change();
            $table->string('fee')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('amount')->change();
            $table->integer('amount_paid')->change();
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->integer('amount')->change();
            $table->integer('amount_left')->change();
        });

        Schema::table('trades', function (Blueprint $table) {
            $table->integer('amount')->change();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->integer('amount')->change();
            $table->integer('fee')->change();
        });
    }
}
