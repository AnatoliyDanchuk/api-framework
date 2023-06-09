<?php

namespace Framework\Endpoint\EndpointInput;

abstract class ParamPath
{
    protected function __construct(
        private readonly array|string $paramPlacePath,
    )
    {
    }

    final public function getSignature(): string
    {
        return serialize($this->getParamPlace()) . serialize($this->paramPlacePath);
    }

    final public function formatToLog(): array
    {
        return ['paramPlace' => $this->getParamPlace()->name]
            + $this->formatPlacePathToLog();
    }

    abstract protected function getParamPlace(): ParamPlace;

    abstract protected function formatPlacePathToLog(): array;

    abstract public function getRouteCondition(): string;

    abstract public function formatPathToDoc(): string|array;
}