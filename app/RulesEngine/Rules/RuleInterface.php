<?php

namespace App\RulesEngine\Rules;

interface RuleInterface
{
    /**
     * @return \uuf6429\Rune\Rule\RuleInterface[] $conditions
     */
    public function getConditions();

    /**
     * @return \uuf6429\Rune\Action\ActionInterface[] $actions
     */
    public function getActions();
}
