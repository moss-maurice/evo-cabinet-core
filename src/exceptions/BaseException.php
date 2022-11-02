<?php

namespace mmaurice\cabinet\core\exceptions;

use Exception;

class BaseException extends Exception
{
    public function makeResponce()
    {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTrace(),
        ];
    }
}
