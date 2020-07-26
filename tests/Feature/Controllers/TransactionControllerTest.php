<?php

namespace Tests\Feature\Controllers;

use App\Models\Payment;
use App\Models\Trade;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_transaction_can_be_accepted()
    {
        $seller = factory(User::class)->create();
        $buyer = factory(User::class)->create();
        $seller->trades()->create($this->validTradeData());
        $trade = Trade::first();
        $trade->accept($buyer, 1000);
        $transaction =  Transaction::first();

        $response = $this->actingAs($seller, 'api')
                        ->postJson("api/v1/transactions/$transaction->uuid/accept");
        $transaction->refresh();

        $response->assertStatus(201);
        $this->assertCount(2, Transaction::all());
        $this->assertEquals(Transaction::STATUS_ACCEPTED, $transaction->status);

        $correspondingTransaction = Transaction::find(2);

        $this->assertEquals($transaction->isBuy ? Transaction::TYPE_SELL : Transaction::TYPE_BUY, $correspondingTransaction->type);
        $this->assertEquals($transaction->trade->from_currency, $correspondingTransaction->currency);
        $this->assertEquals(Transaction::STATUS_ACCEPTED, $correspondingTransaction->status);
        $this->assertEquals($buyer->id, $correspondingTransaction->buyer_id);
        $this->assertEquals($seller->id, $correspondingTransaction->seller_id);

        $this->assertCount(2, Payment::all());
        
        $payment1 = Payment::find(1);
        $payment2 = Payment::find(2);

        $this->assertEquals(Payment::STATUS_DRAFT, $payment1->status);
        $this->assertEquals(Payment::STATUS_DRAFT, $payment2->status);
        $this->assertNull($payment1->paid_at);
        $this->assertNull($payment2->paid_at);
        $this->assertNotEquals($payment1->user_id, $payment2->user_id);
        $this->assertNotEquals($payment1->currency, $payment2->currency);
        $this->assertNotEquals($payment1->transaction_id, $payment2->transaction_id);
        $this->assertNotEquals($payment1->reference_no, $payment2->reference_no);
    }

    private function validTradeData()
    {
        return [
            'amount' => 1000,
            'from_currency' => 'cad',
            'to_currency' => 'ngn',
            'rate'  => 245,
        ];
    }
}
