<?php

namespace App\Listeners;

use App\Events\TradeTransactionsAccepted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateTradeTransactionsPayments
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
        foreach($event->trade->transactions()->accepted()->withoutPayment()->get() as $transaction) {
            $transaction->createPayment();
        }
    }
}
