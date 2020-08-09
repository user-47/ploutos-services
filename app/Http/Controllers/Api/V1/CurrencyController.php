<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    /**
     * Get supported currencies
     */
    public function index(Request $request)
    {
        return response(['data' => Currency::AVAILABLE_CURRENCIES]);
    }
}
