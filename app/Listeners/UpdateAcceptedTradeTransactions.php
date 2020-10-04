<?php

namespace App\Listeners;

use App\Events\TradeTransactionsAccepted;
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
     * @param  TradeTransactionsAccepted  $event
     * @return void
     */
    public function handle(TradeTransactionsAccepted $event)
    {
        $trade = $event->trade;

        $trade->status = $trade->availableAmount == 0 ? Trade::STATUS_FULFILLED : Trade::STATUS_PARTIAL;
        $trade->save();

        if ($trade->isFulfilled) {
            (new TradeManager())->rejectOpenOffers($trade);
        }
    }
}
