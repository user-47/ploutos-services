<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptTransaction;
use App\Http\Resources\TradeRescource;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TransactionController extends Controller
{
    /**
     * Accept a transaction
     * 
     * @param AcceptTransaction $request
     * @return Response
     */
    public function accept(Transaction $transaction, AcceptTransaction $request)
    {
        $sellTransaction = $transaction->accept($request->user());
        return response()->json([
            'trade' => new TradeRescource($sellTransaction->trade),
            'transaction' => new TransactionResource($sellTransaction->refresh()),
        ], Response::HTTP_CREATED);
    }
}
