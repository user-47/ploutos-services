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
