<?php

namespace Tests\Feature\Controllers;

use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CurrencyControllerTest extends TestCase
{
    /** @test */
    public function can_view_list_of_supported_currencies()
    {

        $response = $this->getJson('api/v1/currencies');

        $response->assertStatus(200);
        
        $this->assertCount(count(Currency::AVAILABLE_CURRENCIES), $response['data']);
    }
}
