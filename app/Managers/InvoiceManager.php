<?php

namespace App\Managers;

use App\Models\Invoice;
use App\Payments\Contracts\PaymentGatewaySelector;
use App\Result\Contracts\Result as ContractsResult;
use App\Result\Result;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class InvoiceManager
{
    /** 
     * @var \App\Payments\Contracts\PaymentGatewaySelector handles determing PaymentGateway to be used for processing invoice.
     */
    protected $paymentGatewaySelector;

    public function __construct(PaymentGatewaySelector $paymentGatewaySelector = null) {
        $this->paymentGatewaySelector = $paymentGatewaySelector;
    }

    /**
     * Attempts to pay an invoice
     */
    public function payInvoice(int $invoiceId): ContractsResult
    {
        $invoice = Invoice::find($invoiceId);

        if (is_null($invoice)) {
            return new Result(false, null);
        }

        try {
            // Get payment gateway to use
            $paymentGateway = $this->paymentGatewaySelector->determinePaymentGateway($invoice);
            // Attempt to pay
            $result = $paymentGateway->pay($invoice);

            if (!$result->isSuccess()) {
                return new Result(false, $invoice);
            }

            // if successful
            $invoice->markAsPaid();
        } catch (Exception $e) {
            Log::error("Error paying invoice {$invoice->id} via payment gateway : {$e->getMessage()}");
            // Todo:: Determine invoice failure strategy
            $invoice->markAsFailed();
            return new Result(false, null);
        }

        return new Result($result->isSuccess(), $invoice);
    }

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
