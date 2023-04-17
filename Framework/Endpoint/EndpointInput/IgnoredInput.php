<?php

namespace Framework\Endpoint\EndpointInput;

final class IgnoredInput
{
    public readonly array $params;

    public function __construct(
        FoundInputParam ...$params
    )
    {
        $this->params = $params;
    }
}