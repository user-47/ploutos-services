<?php

namespace App\Managers;

use App\Models\Trade;
use App\Models\Transaction;
use App\Models\User;

class TradeManager
{
    public function create(User $user, array $tradeData)
    {
        // convert amount to lowest unit when storing
        $tradeData['amount'] = CurrencyManager::toMinor($tradeData['amount'], $tradeData['from_currency']);
        return $user->trades()->create($tradeData);
    }

    public function accept(Trade $trade, User $user, $amount): Transaction
    {
        // convert amount to lowest unit when storing
        $amount = CurrencyManager::toMinor($amount, $trade->from_currency);
        return $trade->accept($user, $amount);
    }
}