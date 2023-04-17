<?php

namespace Framework\Exception;

use Throwable;

final class UnexpectedEndpointError extends ExceptionWithContext
{
    private array $inputParams;

    public function __construct(
        string $httpMethod,
        string $httpPath,
        array $inputParams,
        Throwable $caughtException
    ) {
        $this->inputParams = $inputParams;
        $context = [
            'input' => [
                'httpMethod' => $httpMethod,
                'httpPath' => $httpPath,
            ] + $inputParams,
        ];
        parent::__construct($context, $caughtException);
    }

    public function getInputParams(): array
    {
        return $this->inputParams;
    }
}
