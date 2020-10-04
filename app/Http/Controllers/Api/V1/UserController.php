<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TradeCollection;
use App\Http\Resources\TransactionCollection;
use App\Managers\TradeManager;
use App\Managers\TransactionManager;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return $user;
    }

    /**
     * Get all trades for current user
     */
    public function trades(Request $request)
    {
        /** @var User */
        $user = auth()->user();
        $trades = TradeManager::filterTradeQuery($user->trades()->getQuery(), $request);
        $trades = TradeManager::orderTradeQuery($trades, $request);
        $trades = TradeManager::paginateTradeQuery($trades, $request);
        return new TradeCollection($trades);
    }

    /**
     * Get all transactions for current user
     */
    public function transactions(Request $request)
    {
        /** @var User */
        $user = auth()->user();
        $transactions = TransactionManager::filterTransactionQuery($user->transactions(), $request);
        $transactions = TransactionManager::orderTransactionQuery($transactions, $request);
        $transactions = TransactionManager::paginateTransactionQuery($transactions, $request);
        return new TransactionCollection($transactions);
    }
}
