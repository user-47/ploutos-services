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
        $seller = factory(User::class)->create();

        $transaction = Transaction::make([
            'seller_id' => $seller->id,
            'buyer_id' => (factory(User::class)->create())->id,
            'amount' => 1000,
            'currency' => 'ngn',
            'type' => Transaction::TYPE_BUY,
        ]);

        $transaction->trade_id = (factory(Trade::class)->create(['user_id' => $seller->id]))->id;
        $transaction->save();

        $this->assertEquals(Transaction::STATUS_OPEN, Transaction::first()->status);
    }
}
