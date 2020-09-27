<?php

namespace App\RulesEngine\Rules;

use App\RulesEngine\Contexts\TransactionContext;
use uuf6429\Rune\Action\CallbackAction;
use uuf6429\Rune\Rule\GenericRule;

class BaseRule implements RuleInterface
{
    /**
     * Array of Rune Rules acting as Conditions
     * @var \uuf6429\Rune\Rule\RuleInterface[]
     */
    protected $conditions;

    /**
     * Array of Rune Actions
     * @var \uuf6429\Rune\Action\ActionInterface[]
     */
    protected $actions;

    /**
     * @return \uuf6429\Rune\Rule\RuleInterface[] $conditions
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @return \uuf6429\Rune\Action\ActionInterface[] $actions
     */
    public function getActions()
    {
        return $this->actions;
    }
}
