<?php

namespace Framework\Endpoint\EndpointInput;

abstract class InputParam
{
    public function __construct(
        public readonly ParamPath $paramPath,
        public readonly mixed $value,
    )
    {
    }

    abstract public function getFormattedValue(): string|array;
}