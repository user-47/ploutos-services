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
    public function a_trade_request_must_use_supported_currencies()
    {
        $user = factory(User::class)->create();
        $response = $this->actingAs($user, 'api')
            ->postJson('api/v1/trades', [
                'amount' => 1000,
                'from_currency' => 'random',
                'to_currency' => 'veryrandom',
                'rate'  => 230
            ]);

        $response->assertJsonValidationErrors('from_currency');
        $response->assertJsonValidationErrors('to_currency');
    }

    /** @test */
    public function a_trade_request_must_use_different_currencies()
    {
        $user = factory(User::class)->create();
        $response = $this->actingAs($user, 'api')
            ->postJson('api/v1/trades', [
                'amount' => 1000,
                'from_currency' => 'ngn',
                'to_currency' => 'ngn',
                'rate'  => 230
            ]);

        $response->assertJsonValidationErrors('from_currency');
        $response->assertJsonValidationErrors('to_currency');
    }

    /** @test */
    public function a_trade_request_can_be_placed()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('api/v1/trades', $this->validTradeData());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'user',
                'amount',
                'from_currency',
                'to_currency',
                'rate',
                'status',
                'created_at',
            ])
            ->assertJson([
                'amount' => 1000,
                'from_currency' => 'cad',
                'to_currency' => 'ngn',
                'rate' => 245,
                'status' => Trade::STATUS_OPEN,
            ]);

        $this->assertCount(1, Trade::all());
    }

    /** @test */
    public function can_view_paginated_list_of_trades()
    {
        factory(Trade::class, 10)->create(['user_id' => (factory(User::class)->create())->id,]);

        $response = $this->getJson('api/v1/trades?size=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user',
                        'amount',
                        'from_currency',
                        'to_currency',
                        'rate',
                        'status',
                        'created_at',
                    ]
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'path',
                    'per_page',
                    'to',
                    'total',
                ]
            ]);
        
        $this->assertEquals(10, $response['meta']['total']);
        $this->assertCount(5, $response['data']);
    }

    /** @test */
    public function only_authenticated_users_can_accept_a_trade_request()
    {
        $seller = factory(User::class)->create();
        $seller->trades()->create($this->validTradeData());
        $trade = Trade::first();

        $response = $this->postJson("api/v1/trades/$trade->uuid/accept", [
                'amount' => 1000,
            ]);

        $response->assertStatus(401);
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
    public function a_none_open_trade_request_cannot_be_accepted()
    {
        $seller = factory(User::class)->create();
        $buyer = factory(User::class)->create();
        $seller->trades()->create($this->validTradeData());
        $trade = Trade::first();
        $trade->status = Trade::STATUS_FULFILLED;
        $trade->save();

        $response = $this->actingAs($buyer, 'api')
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

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'user',
                'amount',
                'from_currency',
                'to_currency',
                'rate',
                'status',
                'created_at',
                'transactions' => [
                    '*' => [
                        'id',
                        'seller',
                        'buyer',
                        'amount',
                        'currency',
                        'type',
                        'status'
                    ]
                ]
            ])
            ->assertJsonCount(1, 'transactions')
            ->assertJson([
                'status' => Trade::STATUS_FULFILLED,
            ]);

        $transaction = $response->json('transactions')[0];

        $this->assertEquals($trade->uuid, $transaction['trade']);
        $this->assertEquals($trade->user->uuid, $transaction['seller']['id']);
        $this->assertEquals($buyer->uuid, $transaction['buyer']['id']);
        $this->assertEquals(1000, $transaction['amount']);
        $this->assertEquals($trade->from_currency, $transaction['currency']);
        $this->assertEquals(Transaction::TYPE_BUY, $transaction['type']);
        $this->assertEquals(Transaction::STATUS_OPEN, $transaction['status']);
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
