<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptTrade;
use App\Http\Requests\NewTrade;
use App\Http\Resources\TradeCollection;
use App\Http\Resources\TradeRescource;
use App\Http\Resources\TransactionCollection;
use App\Http\Resources\TransactionResource;
use App\Managers\TradeManager;
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
        $trades = TradeManager::filterTradeQuery(Trade::query(), $request);
        $trades = TradeManager::orderTradeQuery($trades, $request);
        $trades = TradeManager::paginateTradeQuery($trades, $request);
        return new TradeCollection($trades);
    }

    /**
     * Store a new trade
     * 
     * @param NewTrade $request
     * @return Response
     */
    public function store(NewTrade $request)
    {
        $trade = (new TradeManager())->create($request->user(), $request->validated());
        return response()->json([
            'success' => true, 
            'data' => [
                'trade' => new TradeRescource($trade->fresh()),
            ],
        ], Response::HTTP_CREATED);
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
            $transaction = (new TradeManager())->accept($trade, $request->user(), $request->amount);
            return response()->json([
                'success' => true,
                'data' => [
                    'trade' => new TradeRescource($transaction->trade),
                    'transaction' => new TransactionResource($transaction->refresh()),
                ],
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error accepting trade.',
                'errors' => [
                    'request' => $e->getMessage()
                ],
            ], Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Cancel a trade request
     * 
     * @return Response
     */
    public function cancel(Request $request, Trade $trade)
    {
        try {
            (new TradeManager())->cancel($trade, $request->user());
            return response()->json([
                'success' => true,
                'data' => [
                    'trade' => new TradeRescource($trade->refresh()),
                ],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling trade.',
                'errors' => [
                    'request' => $e->getMessage()
                ],
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
