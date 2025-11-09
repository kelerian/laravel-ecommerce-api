<?php

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    public function __construct(
        string $message = "",
        protected array $data = [],
        int $code = 422
    ) {
        parent::__construct($message, $code);
    }

    public function getData(): array
    {
        return $this->data;
    }
}
