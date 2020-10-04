<?php

namespace App\Listeners;

use App\Events\TransactionAccepted;
use App\Managers\TradeManager;
use App\Models\Trade;
use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateAcceptedTradeTransactions
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  TransactionAccepted  $event
     * @return void
     */
    public function handle(TransactionAccepted $event)
    {
        $trade = $event->transaction->trade;

        $trade->status = $trade->availableAmount == 0 ? Trade::STATUS_FULFILLED : Trade::STATUS_PARTIAL;
        $trade->save();

        if ($trade->isFulfilled) {
            (new TradeManager())->rejectOpenOffers($trade);
        }
    }
}
