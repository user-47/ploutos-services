<?php

namespace App\Managers;

use App\Models\Trade;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TradeManager
{
    /**
     * Create trade from request
     */
    public function create(User $user, array $tradeData)
    {
        // convert amount to lowest unit when storing
        $tradeData['amount'] = CurrencyManager::toMinor($tradeData['amount'], $tradeData['from_currency']);
        return $user->trades()->create($tradeData);
    }

    /**
     * Accept trade with desired amount 
     */
    public function accept(Trade $trade, User $user, $amount): Transaction
    {
        // convert amount to lowest unit when storing
        $amount = CurrencyManager::toMinor($amount, $trade->from_currency);
        return $trade->accept($user, $amount);
    }

    /**
     * Filter trade query 
     */
    public static function filterTradeQuery(Builder $tradeQuery, Request $request): Builder
    {
        $statuses = explode(',', $request->input('status', implode(",", Trade::STATUS_OPEN_VALUES)));

        $tradeQuery->whereIn('status', $statuses);

        $tradeQuery->when($from_currency = $request->input('from_currency'), function($query) use ($from_currency) {
            $query->whereIn('from_currency', explode(',', $from_currency));
        });

        $tradeQuery->when($to_currency = $request->input('to_currency'), function($query) use ($to_currency) {
            $query->whereIn('to_currency', explode(',', $to_currency));
        });

        return $tradeQuery;
    }

    /**
     * Order trade query
     */
    public static function orderTradeQuery(Builder $tradeQuery, Request $request): Builder
    {
        return $tradeQuery->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }

    /**
     * Paginate trade query
     */
    public static function paginateTradeQuery(Builder $tradeQuery, Request $request): LengthAwarePaginator
    {
        $maxLimit = 100;
        $limit = min($request->input('limit', 10), $maxLimit);
        return $tradeQuery->paginate($limit);
    }
}