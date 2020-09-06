<?php

namespace App\Result;

use App\Result\Contracts\Result as ContractsResult;

class Result implements ContractsResult
{
    protected $success;
    protected $value;
    protected $error;

    public function __construct(bool $success = false, $value = null, $error = null) {
        $this->success = $success;
        $this->value = $value;
        $this->error = $error;
    }

    public function isSuccess(): bool {
        return $this->success;
    }

    public function getValue() {
        return $this->value;
    }

    public function getError() {
        return $this->error;
    }
}
