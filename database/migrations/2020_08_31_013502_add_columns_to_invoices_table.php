<?php

use App\Traits\AltersEnumMigtationTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToInvoicesTable extends Migration
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
            $table->integer('amount_paid')->default(0);
            $table->integer('attempt_count')->default(0);
            $table->integer('failure_count')->default(0);
            $table->string('payment_id')->nullable();
            $table->string('charge_id')->nullable();
            $this->alterEnum('invoices', 'status', ['draft', 'paid', 'failed', 'cancelled', 'partial_refund', 'refunded', 'past_due'], 'draft');
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
            $table->dropColumn('amount_paid');
            $table->dropColumn('attempt_count');
            $table->dropColumn('failure_count');
            $table->dropColumn('payment_id');
            $table->dropColumn('charge_id');
            $this->alterEnum('invoices', 'status', ['draft', 'paid', 'failed', 'cancelled', 'refunded', 'past_due'], 'draft');
        });
    }
}
