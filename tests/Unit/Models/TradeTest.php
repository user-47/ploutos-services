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
        Trade::create([
            'user_id' => (factory(User::class)->create())->id,
            'amount' => 1000,
            'from_currency' => 'cad',
            'to_currency' => 'ngn',
            'rate'  => 245
        ]);

        $this->assertEquals(Trade::STATUS_OPEN, Trade::first()->status);
    }
}
