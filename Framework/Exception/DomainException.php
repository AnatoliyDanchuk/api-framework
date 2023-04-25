<?php

namespace Framework\Exception;

use RuntimeException;
use Throwable;

abstract class DomainException extends RuntimeException
{
    protected function __construct(string $message, Throwable $previousException = null)
    {
        parent::__construct($message, 0, $previousException);
    }
}