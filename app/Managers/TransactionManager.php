<?php

namespace App\Managers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TransactionManager
{

    /**
     * Filter transaction query 
     */
    public static function filterTransactionQuery(Builder $transactionQuery, Request $request): Builder
    {
        $statuses = explode(',', $request->input('status', implode(",", Transaction::STATUS_ALL_STATUSES)));

        $transactionQuery->whereIn('status', $statuses);

        $transactionQuery->when($currency = $request->input('currency'), function($query) use ($currency) {
            $query->whereIn('currency', explode(',', $currency));
        });

        $transactionQuery->when($type = $request->input('type'), function($query) use ($type) {
            $query->whereIn('type', explode(',', $type));
        });

        return $transactionQuery;
    }

    /**
     * Order transaction query
     */
    public static function orderTransactionQuery(Builder $transactionQuery, Request $request): Builder
    {
        return $transactionQuery->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }

    /**
     * Paginate transaction query
     */
    public static function paginateTransactionQuery(Builder $transactionQuery, Request $request): LengthAwarePaginator
    {
        $maxLimit = 100;
        $limit = min($request->input('limit', 10), $maxLimit);
        return $transactionQuery->paginate($limit);
    }
}