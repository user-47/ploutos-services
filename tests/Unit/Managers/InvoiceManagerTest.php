<?php

namespace Tests\Unit\Managers;

use App\Managers\InvoiceManager;
use App\Payments\Contracts\PaymentGateway;
use App\Models\Invoice;
use App\Models\Trade;
use App\Models\User;
use App\Payments\Contracts\PaymentGatewaySelector;
use App\Result\Result;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceManagerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_pay_invoice_using_payment_gateway_successfully()
    {
        $invoice = $this->setupInvoices()->first();

        $paymentGateway = $this->getMockBuilder(PaymentGateway::class)->getMock();
        $paymentGateway->method('pay')->willReturn(new Result(true, $invoice));

        $paymentGatewaySelector = $this->getMockBuilder(PaymentGatewaySelector::class)->getMock();
        $paymentGatewaySelector->method('determinePaymentGateway')->willReturn($paymentGateway);

        $invoiceManager = new InvoiceManager($paymentGatewaySelector);
        $result = $invoiceManager->payInvoice($invoice->id);

        $this->assertTrue($result->isSuccess());

        $invoice = $result->getValue();

        $this->assertEquals(Invoice::STATUS_PAID, $invoice->status);
    }

    /** @test */
    public function can_handle_payment_gateway_invoice_payment_failure()
    {
        $invoice = $this->setupInvoices()->first();

        $paymentGateway = $this->getMockBuilder(PaymentGateway::class)->getMock();
        $paymentGateway->method('pay')->willReturn(new Result(false, $invoice));

        $paymentGatewaySelector = $this->getMockBuilder(PaymentGatewaySelector::class)->getMock();
        $paymentGatewaySelector->method('determinePaymentGateway')->willReturn($paymentGateway);

        $invoiceManager = new InvoiceManager($paymentGatewaySelector);
        $result = $invoiceManager->payInvoice($invoice->id);

        $this->assertFalse($result->isSuccess());

        $invoice = $result->getValue();

        $this->assertEquals(Invoice::STATUS_DRAFT, $invoice->status);
    }

    private function setupInvoices($attributes = []): Collection
    {
        $seller = factory(User::class)->create();
        $buyer = factory(User::class)->create();

        $trade = Trade::create(array_merge([
            'user_id' => $seller->id,
            'amount' => 1000,
            'from_currency' => 'cad',
            'to_currency' => 'ngn',
            'rate'  => 245
        ], $attributes));
        $transaction1 = $trade->refresh()->accept($buyer, 1000);
        $transaction1->refresh()->accept($seller);

        return Invoice::all();
    }
}
