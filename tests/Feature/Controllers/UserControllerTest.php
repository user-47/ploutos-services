<?php

namespace Tests\Feature\Controllers;

use App\Managers\TradeManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_view_trades_owned()
    {
        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        (new TradeManager())->create($user1, $this->validTradeData());
        (new TradeManager())->create($user2, $this->validTradeData());

        $response = $this->actingAs($user1, 'api')->getJson('api/v1/users/trades');
        $this->assertEquals(1, $response['meta']['total']);

        $response = $this->actingAs($user2, 'api')->getJson('api/v1/users/trades');
        $this->assertEquals(1, $response['meta']['total']);
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
