<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptTransaction;
use App\Models\Transaction;
use Illuminate\Http\Request;

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
        return $transaction->accept($request->user());
    }
}
