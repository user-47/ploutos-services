<?php

namespace Tests\Feature\Controllers;

use App\Managers\CurrencyManager;
use App\Managers\TradeManager;
use App\Models\Trade;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
                ],
            ])
            ->assertJson([
                'data' => [
                    'trade' => [
                        'from_currency' => 'cad',
                        'to_currency' => 'ngn',
                        'rate' => 245,
                        'status' => Trade::STATUS_OPEN,
                    ]
                ],
            ]);

        $this->assertCount(1, Trade::all());
    }

    /** @test */
    public function can_view_paginated_list_of_trades()
    {
        factory(Trade::class, 10)->create(['user_id' => (factory(User::class)->create())->id,]);

        $response = $this->getJson('api/v1/trades?limit=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user',
                        'trade_amount',
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
    public function can_view_paginated_list_of_trades_with_filters()
    {
        factory(Trade::class, 5)->create(['user_id' => (factory(User::class)->create())->id, 'status' => Trade::STATUS_OPEN]);
        factory(Trade::class, 3)->create(['user_id' => (factory(User::class)->create())->id, 'status' => Trade::STATUS_PARTIAL]);
        factory(Trade::class, 7)->create(['user_id' => (factory(User::class)->create())->id, 'status' => Trade::STATUS_FULFILLED]);
        factory(Trade::class, 10)->create(['user_id' => (factory(User::class)->create())->id, 'status' => Trade::STATUS_CANCELLED]);

        $response = $this->getJson('api/v1/trades?limit=5&status=open');
        $this->assertEquals(5, $response['meta']['total']);

        $response = $this->getJson('api/v1/trades?limit=5&status=open,partial');
        $this->assertEquals(8, $response['meta']['total']);

        $response = $this->getJson('api/v1/trades?limit=5&status=cancelled,fulfilled');
        $this->assertEquals(17, $response['meta']['total']);

        $response = $this->getJson('api/v1/trades?limit=5&from_currency=cad');
        $this->assertEquals(Trade::acceptable()->where('from_currency', 'cad')->count(), $response['meta']['total']);

        $response = $this->getJson('api/v1/trades?limit=5&to_currency=cad');
        $this->assertEquals(Trade::acceptable()->whereIn('to_currency', ['cad'])->count(), $response['meta']['total']);
    }

    /** @test */
    public function only_authenticated_users_can_accept_a_trade_request()
    {
        $seller = factory(User::class)->create();
        (new TradeManager())->create($seller, $this->validTradeData());
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
        (new TradeManager())->create($seller, $this->validTradeData());
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
        (new TradeManager())->create($seller, $this->validTradeData());
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
        (new TradeManager())->create($seller, $this->validTradeData());
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
        (new TradeManager())->create($seller, $this->validTradeData());
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
        (new TradeManager())->create($seller, $this->validTradeData());
        $trade = Trade::first();

        $response = $this->actingAs($buyer, 'api')
            ->postJson("api/v1/trades/$trade->uuid/accept", [
                'amount' => 1000,
            ]);

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
                        'available_amount',
                        'accepted_offers_count',
                        'open_offers_count',
                        'rejected_offers_count',
                    ],
                    'transaction' => [
                        'id',
                        'seller',
                        'buyer',
                        'trade',
                        'transaction_amount',
                        'currency',
                        'type',
                        'status',
                        'created_at'
                    ],
                ]
            ])
            ->assertJson([
                'data' => [
                    'trade' => [
                        'status' => Trade::STATUS_OPEN,
                    ],
                ],
            ]);
        $trade->refresh();

        $this->assertCount(1, $trade->openOffers);
        $transaction = $trade->openoffers[0];
        $this->assertEquals($trade->uuid, $transaction->trade->uuid);
        $this->assertEquals($trade->user->uuid, $transaction->seller->uuid);
        $this->assertEquals($buyer->uuid, $transaction->buyer->uuid);
        $this->assertEquals(CurrencyManager::toMinor(1000, 'cad'), $transaction->amount);
        $this->assertEquals($trade->from_currency, $transaction->currency);
        $this->assertEquals(Transaction::TYPE_BUY, $transaction->type);
        $this->assertEquals(Transaction::STATUS_OPEN, $transaction->status);
    }

     /** @test */
     public function a_trade_request_can_be_partially_accepted()
     {
         $seller = factory(User::class)->create();
         $buyer = factory(User::class)->create();
         (new TradeManager())->create($seller, $this->validTradeData());
         $trade = Trade::first();
 
         $response = $this->actingAs($buyer, 'api')
             ->postJson("api/v1/trades/$trade->uuid/accept", [
                 'amount' => 500,
             ]);
 
         $response->assertStatus(201)
             ->assertJsonStructure([
                'success',
                'data' => [
                    'trade',
                    'transaction',
                ],
             ])
             ->assertJson([
                'data' => [
                    'trade' => [
                        'status' => Trade::STATUS_OPEN,
                    ],
                ]
             ]);
         $trade->refresh();
 
         $this->assertCount(1, $trade->openOffers);
         $transaction = $trade->openoffers[0];
         $this->assertEquals($trade->uuid, $transaction->trade->uuid);
         $this->assertEquals($trade->user->uuid, $transaction->seller->uuid);
         $this->assertEquals($buyer->uuid, $transaction->buyer->uuid);
         $this->assertEquals(CurrencyManager::toMinor(500, 'cad'), $transaction->amount);
         $this->assertEquals($trade->from_currency, $transaction->currency);
         $this->assertEquals(Transaction::TYPE_BUY, $transaction->type);
         $this->assertEquals(Transaction::STATUS_OPEN, $transaction->status);
     }

     /** @test */
    public function a_trade_request_can_be_accepted_by_multiple_buyers()
    {
        $seller = factory(User::class)->create();
        $buyer = factory(User::class)->create();
        $buyer2 = factory(User::class)->create();
        $buyer3 = factory(User::class)->create();
        (new TradeManager())->create($seller, $this->validTradeData());
        $trade = Trade::first();

        $this->actingAs($buyer, 'api')
            ->postJson("api/v1/trades/$trade->uuid/accept", [
                'amount' => 1000,
            ]);
        
        $this->actingAs($buyer2, 'api')
            ->postJson("api/v1/trades/$trade->uuid/accept", [
                'amount' => 500,
            ]);

        $this->actingAs($buyer3, 'api')
            ->postJson("api/v1/trades/$trade->uuid/accept", [
                'amount' => 100,
            ]);

        $trade->refresh();

        $this->assertCount(3, $trade->openOffers);
        $this->assertEquals($buyer->uuid, $trade->openoffers[0]->buyer->uuid);
        $this->assertEquals($buyer2->uuid, $trade->openoffers[1]->buyer->uuid);
        $this->assertEquals($buyer3->uuid, $trade->openoffers[2]->buyer->uuid);
        $this->assertEquals(CurrencyManager::toMinor(1000, 'cad'), $trade->openoffers[0]->amount);
        $this->assertEquals(CurrencyManager::toMinor(500, 'cad'), $trade->openoffers[1]->amount);
        $this->assertEquals(CurrencyManager::toMinor(100, 'cad'), $trade->openoffers[2]->amount);
    }

    /** @test */
    public function a_user_can_not_accept_a_trade_request_when_he_has_an_open_offer()
    {
        $seller = factory(User::class)->create();
        $buyer = factory(User::class)->create();
        (new TradeManager())->create($seller, $this->validTradeData());
        $trade = Trade::first();

        $this->actingAs($buyer, 'api')
            ->postJson("api/v1/trades/$trade->uuid/accept", [
                'amount' => 700,
            ]);
        
        $response = $this->actingAs($buyer, 'api')
            ->postJson("api/v1/trades/$trade->uuid/accept", [
                'amount' => 500,
            ]);

        $trade->refresh();

        $response->assertJsonValidationErrors('request');
        $this->assertCount(1, $trade->openOffers);
        $this->assertEquals(CurrencyManager::toMinor(700, 'cad'), $trade->openoffers[0]->amount);
    }

    /** @test */
    public function a_trade_request_can_be_accepted_for_available_amount()
    {
        $seller = factory(User::class)->create();
        $buyer = factory(User::class)->create();
        (new TradeManager())->create($seller, $this->validTradeData());
        $trade = Trade::first();
        (new TradeManager())->accept($trade, $buyer, 700);
        $trade->openOffers[0]->accept($seller);

        $response = $this->actingAs($buyer, 'api')
            ->postJson("api/v1/trades/$trade->uuid/accept", [
                'amount' => 500,
            ]);

        $response->assertJsonValidationErrors('amount');
    }

    /** @test */
    public function can_get_trade_transactions()
    {
        $seller = factory(User::class)->create();
        $buyer = factory(User::class)->create();
        (new TradeManager())->create($seller, $this->validTradeData());
        $trade = Trade::first();
        (new TradeManager())->accept($trade, $buyer, 1000);
        $transaction = Transaction::first();
        $transaction->accept($seller);

        $response = $this->actingAs($seller, 'api')
            ->getJson("api/v1/trades/$trade->uuid/transactions");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'seller',
                        'buyer',
                        'transaction_amount',
                        'currency',
                        'type',
                        'status',
                        'created_at'
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
    }

    /** @test */
    public function only_buyer_or_seller_can_get_trade_transactions()
    {
        $seller = factory(User::class)->create();
        $buyer = factory(User::class)->create();
        (new TradeManager())->create($seller, $this->validTradeData());
        $trade = Trade::first();
        (new TradeManager())->accept($trade, $buyer, 1000);
        $transaction = Transaction::first();
        $transaction->accept($seller);

        $random = factory(User::class)->create();

        $response = $this->actingAs($random, 'api')
            ->getJson("api/v1/trades/$trade->uuid/transactions");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta'
            ]);
        $this->assertCount(0, $response->json()['data']);
    }

    /** @test */
    public function a_trade_request_can_be_cancelled_by_owner_if_open()
    {
        $seller = factory(User::class)->create();
        (new TradeManager())->create($seller, $this->validTradeData());
        $trade = Trade::first();

        $this->actingAs($seller, 'api')->postJson("api/v1/trades/$trade->uuid/cancel");

        $trade->refresh();
        $this->assertEquals(Trade::STATUS_CANCELLED, $trade->status);
    }

    /** @test */
    public function a_user_can_not_cancel_a_trade_request_that_is_not_open()
    {
        $seller = factory(User::class)->create();
        (new TradeManager())->create($seller, $this->validTradeData());
        $trade = Trade::first();
        $trade->status = Trade::STATUS_PARTIAL;
        $trade->save();

        $response = $this->actingAs($seller, 'api')
            ->postJson("api/v1/trades/$trade->uuid/cancel");

        $trade->refresh();

        $response->assertJsonValidationErrors('request');
        $this->assertEquals(Trade::STATUS_PARTIAL, $trade->status);
    }

    /** @test */
    public function a_user_can_not_cancel_a_trade_belonging_to_another_user()
    {
        $seller = factory(User::class)->create();
        $user = factory(User::class)->create();
        (new TradeManager())->create($seller, $this->validTradeData());
        $trade = Trade::first();

        $response = $this->actingAs($user, 'api')
            ->postJson("api/v1/trades/$trade->uuid/cancel");

        $trade->refresh();

        $response->assertJsonValidationErrors('request');
        $this->assertEquals(Trade::STATUS_OPEN, $trade->status);
    }

    /** @test */
    public function a_cancelled_trade_request_rejects_all_open_offers()
    {
        $seller = factory(User::class)->create();
        $buyer = factory(User::class)->create();
        (new TradeManager())->create($seller, $this->validTradeData());
        $trade = Trade::first();
        $transaction = (new TradeManager())->accept($trade, $buyer, 500);

        $this->actingAs($seller, 'api')->postJson("api/v1/trades/$trade->uuid/cancel");

        $trade->refresh();
        $transaction->refresh();
        $this->assertEquals(Trade::STATUS_CANCELLED, $trade->status);
        $this->assertCount(0, $trade->openOffers);
        $this->assertEquals(Transaction::STATUS_REJECTED, $transaction->status);
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
