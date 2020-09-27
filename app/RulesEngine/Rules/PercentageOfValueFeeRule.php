<?php

namespace App\RulesEngine\Rules;

use App\Managers\CurrencyManager;
use App\RulesEngine\Contexts\TransactionContext;
use uuf6429\Rune\Action\CallbackAction;
use uuf6429\Rune\Rule\GenericRule;

class PercentageOfValueFeeRule extends BaseRule
{
    public function __construct()
    {
        $this->conditions = [
            new GenericRule(1, '1.5% Fee', 'transaction.amount > 0'),
        ];

        $this->actions = [
            new CallbackAction(function ($eval, TransactionContext $context, GenericRule $rule) {
                $transaction = $context->transaction;
                $amount = CurrencyManager::ofMinor($transaction->amount, $transaction->currency);
                $fee = (1.5/100) * $amount;
                $transaction->fee = CurrencyManager::toMinor($fee, $transaction->currency);
            }),
        ];
    }
}
