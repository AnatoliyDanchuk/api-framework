<?php

namespace Framework\Endpoint\EndpointInput;

final class FoundInputParam extends InputParam
{
    public function getFormattedValue(): string|array
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->paramPath->getSignature();
    }
}