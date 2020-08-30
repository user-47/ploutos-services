<?php

use App\Traits\AltersEnumMigtationTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPastDueOptionToStatusOfInvoiceTable extends Migration
{
    use AltersEnumMigtationTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $this->alterEnum('invoices', 'status', ['draft', 'paid', 'failed', 'cancelled', 'refunded', 'past_due'], 'draft');
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
            $this->alterEnum('invoices', 'status', ['draft', 'paid', 'failed', 'cancelled', 'refunded'], 'draft');
        });
    }
}
