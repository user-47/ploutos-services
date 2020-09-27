<?php

namespace App\RulesEngine\Contexts;

use App\Models\Transaction;
use uuf6429\Rune\Context\ClassContext;

class TransactionContext extends ClassContext
{
    public $transaction;
    
    public function __construct(Transaction $transaction) {   
        $this->transaction = $transaction;
    }
}

