<?php

namespace App\Listeners;

use App\Events\TransactionAccepted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateTradeTransactionsInvoices
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
        foreach($event->transaction->trade->transactions()->accepted()->withoutInvoice()->get() as $transaction) {
            $transaction->createInvoice();
        }
    }
}
