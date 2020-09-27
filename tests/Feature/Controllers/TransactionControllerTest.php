<?php

namespace Tests\Feature\Controllers;

use App\Managers\CurrencyManager;
use App\Managers\TradeManager;
use App\Models\Invoice;
use App\Models\Trade;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_accept_a_transaction()
    {
        $seller = factory(User::class)->create();
        $buyer = factory(User::class)->create();
        (new TradeManager())->create($seller, $this->validTradeData());
        $trade = Trade::first();
        (new TradeManager())->accept($trade, $buyer, 1000);
        $transaction = Transaction::first();

        $response = $this->postJson("api/v1/transactions/$transaction->uuid/accept");

        $response->assertStatus(401);
    }

    /** @test */
    public function a_transaction_cannot_be_accepted_by_the_same_user()
    {
        $seller = factory(User::class)->create();
        $buyer = factory(User::class)->create();
        (new TradeManager())->create($seller, $this->validTradeData());
        $trade = Trade::first();
        (new TradeManager())->accept($trade, $buyer, 1000);
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
        (new TradeManager())->create($seller, $this->validTradeData());
        $trade = Trade::first();
        (new TradeManager())->accept($trade, $buyer, 1000);
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
        (new TradeManager())->create($seller, $this->validTradeData());
        $trade = Trade::first();
        (new TradeManager())->accept($trade, $buyer, 1000);
        $transaction = Transaction::first();

        $response = $this->actingAs($seller, 'api')
            ->postJson("api/v1/transactions/$transaction->uuid/accept");

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'trade' => [
                        'id',
                        'user',
                        'trade_amount',
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
                        'transaction_amount',
                        'currency',
                        'type',
                        'status'
                    ],
                ]
            ])
            ->assertJson([
                'data' => [
                    'trade' => [
                        'status' => Trade::STATUS_FULFILLED,
                    ],
                    'transaction' => [
                        'status' => Transaction::STATUS_ACCEPTED,
                    ],
                ],
            ]);
        $trade->refresh();

        $transaction = $trade->acceptedTransactions[0];
        $correspondingTransaction = $trade->acceptedTransactions[1];

        $this->assertEquals($trade->uuid, $transaction->trade->uuid);
        $this->assertEquals($trade->user->uuid, $transaction->seller->uuid);
        $this->assertEquals($buyer->uuid, $transaction->buyer->uuid);
        $this->assertEquals(CurrencyManager::toMinor(1000, $transaction->currency), $transaction->amount);
        $this->assertEquals($trade->from_currency, $transaction->currency);
        $this->assertEquals(Transaction::TYPE_BUY, $transaction->type);
        $this->assertEquals(Transaction::STATUS_ACCEPTED, $transaction->status);

        $this->assertEquals($seller->uuid, $correspondingTransaction->seller->uuid);
        $this->assertEquals($buyer->uuid, $correspondingTransaction->buyer->uuid);
        $this->assertEquals($trade->from_currency, $correspondingTransaction->currency);
        $this->assertEquals(Transaction::STATUS_ACCEPTED, $correspondingTransaction->status);
        $this->assertEquals(Transaction::TYPE_SELL, $correspondingTransaction->type);


        $invoices = Invoice::all();
        $this->assertCount(2, $invoices);

        $invoice1 = $invoices->first();
        $invoice2 = $invoices->last();

        $this->assertEquals(Invoice::STATUS_DRAFT, $invoice1->status);
        $this->assertEquals(Invoice::STATUS_DRAFT, $invoice2->status);
        $this->assertNull($invoice1->paid_at);
        $this->assertNull($invoice2->paid_at);
        $this->assertNotEquals($invoice1->user_id, $invoice2->user_id);
        $this->assertNotEquals($invoice1->currency, $invoice2->currency);
        $this->assertNotEquals($invoice1->transaction_id, $invoice2->transaction_id);
        $this->assertNotEquals($invoice1->reference_no, $invoice2->reference_no);

        $this->assertEquals($invoice1->transaction->transactionAmount + $invoice1->transaction->transactionFee, $invoice1->amount);
        $this->assertEquals($invoice2->transaction->transactionAmount + $invoice2->transaction->transactionFee, $invoice2->amount);
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
