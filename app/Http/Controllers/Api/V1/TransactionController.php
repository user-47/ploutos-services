<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Accept a transaction
     * 
     * @param Request $request
     * @return Response
     */
    public function accept(Transaction $transaction, Request $request)
    {
        return $transaction->accept($request->user());
    }
}
