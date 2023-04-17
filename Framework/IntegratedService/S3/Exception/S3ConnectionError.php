<?php

namespace Framework\IntegratedService\S3\Exception;

use Framework\Exception\ExceptionWithContext;
use Throwable;

final class S3ConnectionError extends ExceptionWithContext
{
    public function __construct(Throwable $exception)
    {
        parent::__construct([], $exception);
    }
}