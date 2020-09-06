<?php

namespace App\Payments\Contracts;

use App\Models\Invoice;

/**
 * Determines which PaymentGateway should be use for the provided invoice.
 */
interface PaymentGatewaySelector
{
    public function determinePaymentGateway(Invoice $invoice): PaymentGateway;
}
