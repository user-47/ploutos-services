<?php

namespace App\Payments\Contracts;

use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Refund;
use App\Models\User;
use App\Result\Contracts\Result as ContractsResult;

/**
 * Handles paying and refunding invoices for different gateways (Stripe, Braintree, Paypal, etc.)
 */
interface PaymentGateway
{
    /**
     * Creates and returns a payment method for the user from the checkout token received.
     *
     */
    public function authorize(User $user, string $checkoutToken): PaymentMethod;

    /**
     * Attempts to pay an invoice.
     *
     * @param  Invoice  $invoice
     */
    public function pay(Invoice $invoice): ContractsResult;

    /**
     * Attempts to refunds an invoice.
     *
     * @param  Refund   $refund
     */
    public function refund(Refund $refund): ContractsResult;
}