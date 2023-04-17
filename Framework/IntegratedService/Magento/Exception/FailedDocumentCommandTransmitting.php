<?php

namespace Framework\IntegratedService\Magento\Exception;

use Framework\Exception\ExceptionWithContext;
use Throwable;

final class FailedDocumentCommandTransmitting extends ExceptionWithContext
{
    public function __construct(Throwable $exception)
    {
        parent::__construct([
            'last_exception' => $exception->getMessage(),
        ], $exception);
    }
}