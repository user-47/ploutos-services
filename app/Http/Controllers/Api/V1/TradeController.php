<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewTrade;
use App\Models\Trade;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TradeController extends Controller
{
    /**
     * Get all trades
     */
    public function index()
    {
        return response()->json(Trade::all());
    }

    /**
     * Store a new trade
     * 
     * @param NewTrade $request
     * @return Response
     */
    public function store(NewTrade $request)
    {
        return $request->user()->trades()->create($request->validated());
    }

    /**
     * Accept a trade request
     * 
     * @param Request $request
     * @return Response
     */
    public function accept(Trade $trade, Request $request)
    {
        return $trade->accept($request->user(), $request->amount);
    }
}
