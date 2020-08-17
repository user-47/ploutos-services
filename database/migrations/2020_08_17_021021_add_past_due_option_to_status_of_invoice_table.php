<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPastDueOptionToStatusOfInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'paid', 'failed', 'cancelled', 'refunded', 'past_due') default 'draft'");
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
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'paid', 'failed', 'cancelled', 'refunded') default 'draft'");
        });
    }
}
