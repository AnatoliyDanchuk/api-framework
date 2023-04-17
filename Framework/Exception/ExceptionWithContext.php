<?php

namespace Framework\Exception;

use Framework\JsonCoder\ReadableJsonEncoder;
use Throwable;

abstract class ExceptionWithContext extends FrameworkException
{
    private array $context;

    protected function __construct(
        array $context,
        Throwable $caughtException = null
    ) {
        $this->context = $context;
        parent::__construct($caughtException);
        // Used only by built-in Symfony debugger.
        $this->message = (new ReadableJsonEncoder())->encode($this->context);
    }

    public function getContext(): array
    {
        return $this->context;
    }
}