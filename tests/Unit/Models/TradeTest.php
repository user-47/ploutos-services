<?php

namespace Tests\Unit\Models;

use App\Models\Trade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_new_trade_is_open()
    {
        $trade = $this->createTrade();
        $this->assertEquals(Trade::STATUS_OPEN, $trade->status);
    }

    /** @test */
    public function a_trade_can_have_only_supported_currencies()
    {
        $this->expectExceptionMessage("Invalid currencies");
        $this->createTrade(['from_currency' => 'random', 'to_currency' => 'veryrandom']);
        $this->assertCount(0, Trade::all());
    }

    /** @test */
    public function a_trade_can_not_have_same_currencies()
    {
        $this->expectExceptionMessage("Can not place a trade with the same currency");
        $this->createTrade(['from_currency' => 'ngn', 'to_currency' => 'ngn']);
        $this->assertCount(0, Trade::all());
    }

    /** @test */
    public function a_trade_has_exchange_amount()
    {
        $trade = $this->createTrade();
        $this->assertEquals(245000, $trade->exchangeAmount);
    }

    /** @test */
    public function accepting_trade_by_same_user_throws_error()
    {
        $this->expectExceptionMessage("Can not accept a trade you originated.");
        $trade = $this->createTrade();
        $trade->accept($trade->user, 1000);
    }

    /** @test */
    public function accepting_none_open_or_none_partial_trade_throws_error()
    {
        $this->expectExceptionMessage("Can not accept a trade that is not open or partially filled.");
        $trade = $this->createTrade();
        $trade->status = Trade::STATUS_FULFILLED;
        $trade->save();
        $trade->accept(factory(User::class)->create(), 1000);
    }

    /** @test */
    public function a_trade_can_be_accepted()
    {
        $trade = $this->createTrade();
        $trade->accept(factory(User::class)->create(), 1000);
        $trade->offers[0]->accept($trade->user);
        $trade->refresh();

        $this->assertEquals(Trade::STATUS_FULFILLED, $trade->status);
        $this->assertEquals(0, $trade->availableAmount);
    }

    /** @test */
    public function can_accept_some_of_the_amount_on_offer()
    {
        $trade = $this->createTrade();
        $trade->accept(factory(User::class)->create(), 500);
        $trade->offers[0]->accept($trade->user);
        $trade->refresh();
        
        $this->assertEquals(Trade::STATUS_PARTIAL, $trade->status);
        $this->assertEquals(500, $trade->availableAmount);
    }

    /** @test */
    public function multiple_users_can_accept_an_open_trade()
    {
        $trade = $this->createTrade();
        $trade->accept(factory(User::class)->create(), 1000);
        $trade->accept(factory(User::class)->create(), 1000);
        $trade->accept(factory(User::class)->create(), 500);
        $trade->fresh();

        $this->assertEquals(Trade::STATUS_OPEN, $trade->status);
        $this->assertCount(3, $trade->offers);
        $this->assertEquals(1000, $trade->availableAmount);
    }

    /** @test */
    public function multiple_users_can_accept_a_partial_trade()
    {
        $trade = $this->createTrade();
        $trade->accept(factory(User::class)->create(), 500);

        $trade->offers[0]->accept($trade->user);


        $trade->accept(factory(User::class)->create(), 500);
        $trade->accept(factory(User::class)->create(), 500);
        $trade->refresh();

        $this->assertEquals(Trade::STATUS_PARTIAL, $trade->status);
        $this->assertCount(3, $trade->offers);
        $this->assertCount(2, $trade->openOffers);
        $this->assertEquals(500, $trade->availableAmount);
    }

    /** @test */
    public function a_user_can_not_accept_a_trade_multiple_times_when_he_has_an_offer_still_open()
    {
        $this->expectExceptionMessage("Can not accept trade when you still have an offer open.");
        $buyer = factory(User::class)->create();
        $trade = $this->createTrade();
        $trade->accept($buyer, 500);
        $trade->accept($buyer, 500);

        $trade->refresh();

        $this->assertCount(1, $trade->openOffers);
    }

    /** @test */
    public function cancel_open_offers_when_trade_is_fulfilled()
    {
        $trade = $this->createTrade();
        $trade->accept(factory(User::class)->create(), 500);
        $trade->offers[0]->accept($trade->user);
        $trade->accept(factory(User::class)->create(), 500);
        $trade->accept(factory(User::class)->create(), 500);
        $trade->refresh();
        $trade->offers[1]->accept($trade->user);
        $trade->refresh();

        $this->assertEquals(Trade::STATUS_FULFILLED, $trade->status);
        $this->assertCount(3, $trade->offers);
        $this->assertCount(0, $trade->openOffers);
        $this->assertCount(1, $trade->rejectedOffers);
        $this->assertEquals(0, $trade->availableAmount);
    }

    /** @test */
    public function a_trade_can_be_cancelled()
    {
        $trade = $this->createTrade();
        $trade->cancel($trade->user);
        $trade->refresh();

        $this->assertEquals(Trade::STATUS_CANCELLED, $trade->status);
    }

    /** @test */
    public function a_trade_can_not_be_cancelled_by_another_user()
    {
        $this->expectExceptionMessage("Can not cancel a trade not created by you.");
        $trade = $this->createTrade();
        $trade->cancel(factory(User::class)->create());
        $trade->refresh();

        $this->assertEquals(Trade::STATUS_OPEN, $trade->status);
    }

    private function createTrade($attributes = []): Trade
    {
        Trade::create(array_merge([
            'user_id' => (factory(User::class)->create())->id,
            'amount' => 1000,
            'from_currency' => 'cad',
            'to_currency' => 'ngn',
            'rate'  => 245
        ], $attributes));

        return Trade::first();
    }
}
