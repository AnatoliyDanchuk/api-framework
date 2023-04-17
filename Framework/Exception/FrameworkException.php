<?php

namespace Framework\Exception;

use Exception;
use Throwable;

abstract class FrameworkException extends Exception
{
    public function __construct(
        Throwable $caughtException = null
    ) {
        parent::__construct('', 1, $caughtException);
    }
}