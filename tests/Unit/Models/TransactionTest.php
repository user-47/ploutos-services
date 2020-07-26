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
    public function a_transaction_has_payment_amount()
    {
        $transaction = $this->createTransaction();
        $trade = $transaction->trade;
        $this->assertEquals($trade->rate * $transaction->amount, $transaction->paymentAmount);

        $transaction = $this->createTransaction(['type' => Transaction::TYPE_SELL]);
        $trade = $transaction->trade;
        $this->assertEquals($transaction->amount, $transaction->paymentAmount);
    }

    /** @test */
    public function a_transaction_has_payment_currency()
    {
        $transaction = $this->createTransaction();
        $trade = $transaction->trade;
        $this->assertEquals($trade->to_currency, $transaction->paymentCurrency);

        $transaction = $this->createTransaction(['type' => Transaction::TYPE_SELL]);
        $trade = $transaction->trade;
        $this->assertEquals($transaction->currency, $transaction->paymentCurrency);
    }

    private function createTransaction($attributes = [])
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
