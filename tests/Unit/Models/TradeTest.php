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
    public function a_trade_has_exchange_amount()
    {
        $trade = $this->createTrade();
        $this->assertEquals(245000, $trade->exchangeAmount);
    }

    private function createTrade()
    {
        Trade::create([
            'user_id' => (factory(User::class)->create())->id,
            'amount' => 1000,
            'from_currency' => 'cad',
            'to_currency' => 'ngn',
            'rate'  => 245
        ]);

        return Trade::first();
    }
}
