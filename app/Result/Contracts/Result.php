<?php

namespace App\Result\Contracts;

interface Result
{
    public function isSuccess(): bool;

    public function getValue();

    public function getError();
}
