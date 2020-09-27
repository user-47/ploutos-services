<?php

namespace App\RulesEngine\Engines;

use App\Models\Transaction;
use App\RulesEngine\Contexts\TransactionContext;
use App\RulesEngine\Rules\MaximumFeeRule;
use App\RulesEngine\Rules\PercentageOfValueFeeRule;
use uuf6429\Rune\Engine;

class TransactionFeeEngine
{
    protected $engine;

    public function __construct(Engine $engine = null)
    {
        $this->engine = $engine ?: new Engine();
    }

    public function execute(Transaction $transaction)
    {
        // limitation -> manual ordering of application
        $rules = [ 
            new PercentageOfValueFeeRule(),
            new MaximumFeeRule(),
        ];

        // limitation -> currently only executing one action for each rule
        foreach ($rules as $rule) {
            $this->engine->execute(
                new TransactionContext($transaction),
                $rule->getConditions(),
                $rule->getActions()[0]
            );
        }
    }
}
