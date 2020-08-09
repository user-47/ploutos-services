<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptTrade;
use App\Http\Requests\NewTrade;
use App\Http\Resources\TradeCollection;
use App\Http\Resources\TradeRescource;
use App\Http\Resources\TransactionCollection;
use App\Http\Resources\TransactionResource;
use App\Models\Trade;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TradeController extends Controller
{
    /**
     * Get all trades
     */
    public function index(Request $request)
    {
        $sizeLimit = 100;
        $size = min($request->input('size', 10), $sizeLimit);
        return new TradeCollection(Trade::paginate($size));
    }

    /**
     * Store a new trade
     * 
     * @param NewTrade $request
     * @return Response
     */
    public function store(NewTrade $request)
    {
        $trade = $request->user()->trades()->create($request->validated());
        return response()->json(new TradeRescource($trade->fresh()), Response::HTTP_CREATED);
    }

    /**
     * Accept a trade request
     * 
     * @param AcceptTrade $request
     * @return Response
     */
    public function accept(Trade $trade, AcceptTrade $request)
    {
        try {
            $transaction = $trade->accept($request->user(), $request->amount);
            return response()->json([
                'trade' => new TradeRescource($transaction->trade),
                'transaction' => new TransactionResource($transaction->refresh()),
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error accepting trade.',
                'errors' => [
                    'request' => $e->getMessage()
                ]
            ], Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Return a trade's transactions
     * 
     * @return Response
     */
    public function transactions(Trade $trade, Request $request)
    {
        $sizeLimit = 100;
        $size = min($request->input('size', 10), $sizeLimit);
        return new TransactionCollection($trade->transactions()->user($request->user())->paginate($size));
    }
}
