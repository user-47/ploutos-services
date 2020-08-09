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
        $maxLimit = 100;
        $trades = Trade::query();

        $limit = min($request->input('limit', 10), $maxLimit);

        $statuses = explode(',', $request->input('status', implode(",", Trade::STATUS_OPEN_VALUES)));

        $trades->whereIn('status', $statuses);

        $trades->when($from_currency = $request->input('from_currency'), function($query) use ($from_currency) {
            $query->whereIn('from_currency', explode(',', $from_currency));
        });

        $trades->when($to_currency = $request->input('to_currency'), function($query) use ($to_currency) {
            $query->whereIn('to_currency', explode(',', $to_currency));
        });

        $trades->orderBy('created_at', 'desc')->orderBy('id', 'desc');

        return new TradeCollection($trades->paginate($limit));
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
