<?php

namespace Tests\Feature\Controllers;

use App\Models\Trade;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TradeControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_make_a_trade_request()
    {
        $response = $this->postJson('api/v1/trades', $this->validTradeData());

        $response->assertStatus(401);
        $this->assertCount(0, Trade::all());
    }

    /** @test */
    public function a_trade_request_must_have_required_fields()
    {
        $user = factory(User::class)->create();
        $response = $this->actingAs($user, 'api')
            ->postJson('api/v1/trades', [
                'amount' => 0,
                'from_currency' => '',
                'to_currency' => '',
                'rate'  => 0
            ]);

        $response->assertJsonValidationErrors('amount');
        $response->assertJsonValidationErrors('from_currency');
        $response->assertJsonValidationErrors('to_currency');
        $response->assertJsonValidationErrors('rate');
    }

    /** @test */
    public function a_trade_request_can_be_placed()
    {
        $user = factory(User::class)->create();
        
        $response = $this->actingAs($user, 'api')
            ->postJson('api/v1/trades', $this->validTradeData());

        $response->assertStatus(201);
        $this->assertCount(1, Trade::all());

        $trade = Trade::first();

        $this->assertEquals(1000, $trade->amount);
        $this->assertEquals('cad', $trade->from_currency);
        $this->assertEquals('ngn', $trade->to_currency);
        $this->assertEquals(245, $trade->rate);
    }

    /** @test */
    public function a_list_of_trades_can_be_viewed()
    {
        $users = factory(User::class, 3)->create();
        factory(Trade::class, 10)->create(['user_id' => $users->random()->id,]);

        $response = $this->getJson('api/v1/trades');

        $response->assertStatus(200);
        $response->assertJsonCount(10);
    }

    /** @test */
    public function a_trade_request_cannot_be_accepted_by_the_same_user()
    {
        $seller = factory(User::class)->create();
        $seller->trades()->create($this->validTradeData());
        $trade = Trade::first();

        $response = $this->actingAs($seller, 'api')
            ->postJson("api/v1/trades/$trade->uuid/accept", [
                'amount' => 1000,
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function a_trade_request_cannot_be_accepted_with_zero_amount()
    {
        $seller = factory(User::class)->create();
        $buyer = factory(User::class)->create();
        $seller->trades()->create($this->validTradeData());
        $trade = Trade::first();

        $response = $this->actingAs($buyer, 'api')
            ->postJson("api/v1/trades/$trade->uuid/accept", [
                'amount' => 0,
            ]);

        $response->assertJsonValidationErrors('amount');
    }

    /** @test */
    public function a_trade_request_cannot_be_accepted_with_amount_greater_than_trade_amount()
    {
        $seller = factory(User::class)->create();
        $buyer = factory(User::class)->create();
        $seller->trades()->create($this->validTradeData());
        $trade = Trade::first();

        $response = $this->actingAs($buyer, 'api')
            ->postJson("api/v1/trades/$trade->uuid/accept", [
                'amount' => 1001,
            ]);

        $response->assertJsonValidationErrors('amount');
    }

    /** @test */
    public function a_trade_request_can_be_accepted()
    {
        $seller = factory(User::class)->create();
        $buyer = factory(User::class)->create();
        $seller->trades()->create($this->validTradeData());
        $trade = Trade::first();

        $response = $this->actingAs($buyer, 'api')
            ->postJson("api/v1/trades/$trade->uuid/accept", [
                'amount' => 1000,
            ]);

        $response->assertStatus(201);

        $trade->refresh();
        $this->assertEquals(Trade::STATUS_FULFILLED, $trade->status);

        $transaction =  Transaction::first();
        $this->assertEquals($trade->id, $transaction->trade_id);
        $this->assertEquals($trade->user_id, $transaction->seller_id);
        $this->assertEquals($buyer->id, $transaction->buyer_id);
        $this->assertEquals(1000, $transaction->amount);
        $this->assertEquals($trade->from_currency, $transaction->currency);
        $this->assertEquals(Transaction::TYPE_BUY, $transaction->type);
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
