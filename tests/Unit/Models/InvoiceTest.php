<?php

namespace Tests\Unit\Models;

use App\Models\Invoice;
use App\Models\Trade;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function new_invoices_are_in_draft()
    {
        $invoices = $this->setupInvoices();
        $this->assertCount(2, $invoices);
        $this->assertEquals(Invoice::STATUS_DRAFT, $invoices[0]->status);
        $this->assertEquals(Invoice::STATUS_DRAFT, $invoices[1]->status);
    }

    /** @test */
    public function can_mark_invoice_as_paid()
    {
        $invoices = $this->setupInvoices();
        $invoices[0]->markAsPaid();
        $this->assertEquals(Invoice::STATUS_PAID, $invoices[0]->status);
        $this->assertNotNull($invoices[0]->paid_at);
    }

    /** @test */
    public function can_only_mark_draft_invoice_as_paid()
    {
        $this->expectExceptionMessage("Can not mark a non draft invoice as paid");
        $invoices = $this->setupInvoices();
        $invoices[0]->status = Invoice::STATUS_FAILED;
        $invoices[0]->save();
        $invoices[1]->status = Invoice::STATUS_CANCELLED;
        $invoices[1]->save();

        $invoices[0]->refresh()->markAsPaid();
        $this->assertEquals(Invoice::STATUS_FAILED, $invoices[0]->status);

        $invoices[1]->refresh()->markAsPaid();
        $this->assertEquals(Invoice::STATUS_CANCELLED, $invoices[1]->status);
    }

    /** @test */
    public function a_paid_invoice_sets_a_due_date_for_the_reference_invoice()
    {
        $invoice = $this->setupInvoices()->first();
        $invoice->markAsPaid();
        $dueDate = $invoice->paid_at->addHour();
        $this->assertNotNull($invoice->referenceInvoice->due_date);
        $this->assertTrue($dueDate->equalTo($invoice->referenceInvoice->due_date));
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
