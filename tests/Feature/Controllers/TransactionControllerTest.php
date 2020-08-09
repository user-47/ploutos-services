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
    public function only_authenticated_users_can_accept_a_transaction()
    {
        $seller = factory(User::class)->create();
        $buyer = factory(User::class)->create();
        $seller->trades()->create($this->validTradeData());
        $trade = Trade::first();
        $trade->accept($buyer, 1000);
        $transaction = Transaction::first();

        $response = $this->postJson("api/v1/transactions/$transaction->uuid/accept");

        $response->assertStatus(401);
    }

    /** @test */
    public function a_transaction_cannot_be_accepted_by_the_same_user()
    {
        $seller = factory(User::class)->create();
        $buyer = factory(User::class)->create();
        $seller->trades()->create($this->validTradeData());
        $trade = Trade::first();
        $trade->accept($buyer, 1000);
        $transaction = Transaction::first();

        $response = $this->actingAs($buyer, 'api')
            ->postJson("api/v1/transactions/$transaction->uuid/accept");

        $response->assertStatus(403);
    }

    /** @test */
    public function a_none_open_transaction_cannot_be_accepted()
    {
        $seller = factory(User::class)->create();
        $buyer = factory(User::class)->create();
        $seller->trades()->create($this->validTradeData());
        $trade = Trade::first();
        $trade->accept($buyer, 1000);
        $transaction = Transaction::first();
        $transaction->status = Transaction::STATUS_ACCEPTED;
        $transaction->save();

        $response = $this->actingAs($seller, 'api')
            ->postJson("api/v1/transactions/$transaction->uuid/accept");

        $response->assertStatus(403);
    }

    /** @test */
    public function a_transaction_can_be_accepted()
    {
        $seller = factory(User::class)->create();
        $buyer = factory(User::class)->create();
        $seller->trades()->create($this->validTradeData());
        $trade = Trade::first();
        $trade->accept($buyer, 1000);
        $transaction = Transaction::first();

        $response = $this->actingAs($seller, 'api')
            ->postJson("api/v1/transactions/$transaction->uuid/accept");

        $response->assertStatus(201)
            ->assertJsonStructure([
                'trade' => [
                    'id',
                    'user',
                    'amount',
                    'from_currency',
                    'to_currency',
                    'rate',
                    'status',
                    'created_at',
                ],
                'transaction' => [
                    'id',
                    'seller',
                    'buyer',
                    'amount',
                    'currency',
                    'type',
                    'status'
                ]
            ])
            ->assertJson([
                'trade' => [
                    'status' => Trade::STATUS_FULFILLED,
                ],
                'transaction' => [
                    'status' => Transaction::STATUS_ACCEPTED,
                ],
            ]);
        $trade->refresh();

        $transaction = $trade->acceptedTransactions[0];
        $correspondingTransaction = $trade->acceptedTransactions[1];

        $this->assertEquals($trade->uuid, $transaction->trade->uuid);
        $this->assertEquals($trade->user->uuid, $transaction->seller->uuid);
        $this->assertEquals($buyer->uuid, $transaction->buyer->uuid);
        $this->assertEquals(1000, $transaction->amount);
        $this->assertEquals($trade->from_currency, $transaction->currency);
        $this->assertEquals(Transaction::TYPE_BUY, $transaction->type);
        $this->assertEquals(Transaction::STATUS_ACCEPTED, $transaction->status);

        $this->assertEquals($seller->uuid, $correspondingTransaction->seller->uuid);
        $this->assertEquals($buyer->uuid, $correspondingTransaction->buyer->uuid);
        $this->assertEquals($trade->from_currency, $correspondingTransaction->currency);
        $this->assertEquals(Transaction::STATUS_ACCEPTED, $correspondingTransaction->status);
        $this->assertEquals(Transaction::TYPE_SELL, $correspondingTransaction->type);


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
