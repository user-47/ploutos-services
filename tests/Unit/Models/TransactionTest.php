<?php

namespace Tests\Unit\Models;

use App\Models\Trade;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_new_transaction_is_open()
    {
        $transaction = $this->createTransaction();
        $this->assertEquals(Transaction::STATUS_OPEN, $transaction->status);
    }

    /** @test */
    public function a_transaction_has_a_payer()
    {
        $transaction = $this->createTransaction();
        $this->assertEquals($transaction->payer->id, $transaction->buyer_id);

        $transaction = $this->createTransaction(['type' => Transaction::TYPE_SELL]);
        $this->assertEquals($transaction->payer->id, $transaction->seller_id);
    }

    /** @test */
    public function a_transaction_has_invoice_amount()
    {
        $transaction = $this->createTransaction();
        $trade = $transaction->trade;
        $this->assertEquals($trade->rate * $transaction->amount, $transaction->invoiceAmount);

        $transaction = $this->createTransaction(['type' => Transaction::TYPE_SELL]);
        $trade = $transaction->trade;
        $this->assertEquals($transaction->amount, $transaction->invoiceAmount);
    }

    /** @test */
    public function a_transaction_has_invoice_currency()
    {
        $transaction = $this->createTransaction();
        $trade = $transaction->trade;
        $this->assertEquals($trade->to_currency, $transaction->invoiceCurrency);

        $transaction = $this->createTransaction(['type' => Transaction::TYPE_SELL]);
        $trade = $transaction->trade;
        $this->assertEquals($transaction->currency, $transaction->invoiceCurrency);
    }

    /** @test */
    public function accepting_transaction_by_same_user_throws_error()
    {
        $this->expectExceptionMessage("Can not accept a transaction you originated.");
        $transaction = $this->createTransaction();
        $transaction->accept($transaction->buyer);
    }

    /** @test */
    public function accepting_none_open_transaction_throws_error()
    {
        $this->expectExceptionMessage("Can not accept a transaction that is not open.");
        $transaction = $this->createTransaction();
        $transaction->status = Transaction::STATUS_ACCEPTED;
        $transaction->save();
        $transaction->accept($transaction->seller);
    }

    /** @test */
    public function a_transaction_can_be_rejected()
    {
        $transaction = $this->createTransaction();
        $transaction->reject();
        $this->assertEquals(Transaction::STATUS_REJECTED, $transaction->status);
    }

    /** @test */
    public function a_none_open_transaction_can_not_be_rejected()
    {
        $this->expectExceptionMessage("Can not reject a transaction that is not open.");
        $transaction = $this->createTransaction();
        $transaction->status = Transaction::STATUS_ACCEPTED;
        $transaction->save();
        $transaction->reject();
        $this->assertEquals(Transaction::STATUS_ACCEPTED, $transaction->status);
    }


    private function createTransaction($attributes = []): Transaction
    {
        $seller = factory(User::class)->create();

        $transaction = Transaction::make(array_merge([
            'seller_id' => $seller->id,
            'buyer_id' => (factory(User::class)->create())->id,
            'amount' => 1000,
            'currency' => 'ngn',
            'type' => Transaction::TYPE_BUY,
        ], $attributes));

        $transaction->trade_id = (factory(Trade::class)->create(['user_id' => $seller->id]))->id;
        $transaction->save();

        return $transaction->refresh();
    }
}
