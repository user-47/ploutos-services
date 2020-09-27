<?php

namespace App\RulesEngine\Rules;

use App\RulesEngine\Contexts\TransactionContext;
use uuf6429\Rune\Action\CallbackAction;
use uuf6429\Rune\Rule\GenericRule;

class MaximumFeeRule extends BaseRule
{
    public function __construct()
    {
        $this->conditions = [
            new GenericRule(1, 'Maximum Fee Amount', 'transaction.fee > 1000'),
        ];

        $this->actions = [
            new CallbackAction(function ($eval, TransactionContext $context, GenericRule $rule) {
                $transaction = $context->transaction;
                $transaction->fee = 1000;
            }),
        ];
    }
}
