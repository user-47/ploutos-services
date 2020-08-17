<?php

namespace App\Managers;

use App\Models\Invoice;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class InvoiceManager
{
    /**
     * Get invoices whose past due date is in the past and mark as past due
     */
    public function reviewPastDueInvoices()
    {
        // To be done on queue
        $invoiceIds = Invoice::draft()->where('due_date', '<', Carbon::now())->pluck('id');
        foreach ($invoiceIds as $id) {
            try {
                Invoice::find($id)->markAsPastDue();
            } catch (Exception $e) {
                Log::error("Error marking invoice {$id} as past due : {$e->getMessage()}");
            }
        }
    }
}
