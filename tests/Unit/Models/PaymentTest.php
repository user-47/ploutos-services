<?php

namespace Tests\Unit\Models;

use App\Models\Payment;
use App\Models\Trade;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function new_payments_are_in_draft()
    {
        $payments = $this->setupPayments();
        $this->assertCount(2, $payments);
        $this->assertEquals(Payment::STATUS_DRAFT, $payments[0]->status);
        $this->assertEquals(Payment::STATUS_DRAFT, $payments[1]->status);
    }

    /** @test */
    public function can_mark_payment_as_paid()
    {
        $payments = $this->setupPayments();
        $payments[0]->markAsPaid();
        $this->assertEquals(Payment::STATUS_PAID, $payments[0]->status);
    }

    /** @test */
    public function can_only_mark_draft_payment_as_paid()
    {
        $this->expectExceptionMessage("Can not mark a non draft payment as paid");
        $payments = $this->setupPayments();
        $payments[0]->status = Payment::STATUS_FAILED;
        $payments[0]->save();
        $payments[1]->status = Payment::STATUS_CANCELLED;
        $payments[1]->save();

        $payments[0]->refresh()->markAsPaid();
        $this->assertEquals(Payment::STATUS_FAILED, $payments[0]->status);

        $payments[1]->refresh()->markAsPaid();
        $this->assertEquals(Payment::STATUS_CANCELLED, $payments[1]->status);
    }

    private function setupPayments($attributes = []): Collection
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

        return Payment::all();
    }
}
