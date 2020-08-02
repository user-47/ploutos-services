<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptTransaction;
use App\Http\Resources\TradeRescource;
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
        return response()->json(new TradeRescource($transaction->accept($request->user())->trade), Response::HTTP_CREATED);
    }
}
