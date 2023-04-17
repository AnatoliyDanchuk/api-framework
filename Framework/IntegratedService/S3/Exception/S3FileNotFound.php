<?php

namespace Framework\IntegratedService\S3\Exception;

use Aws\S3\Exception\S3Exception;
use Framework\Exception\ExceptionWithContext;

final class S3FileNotFound extends ExceptionWithContext
{
    public function __construct(S3Exception $exception)
    {
        parent::__construct([
            'S3ExceptionMessage' => $exception->getMessage(),
        ], $exception);
    }
}