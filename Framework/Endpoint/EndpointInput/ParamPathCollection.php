<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Exception\ParamShouldBeAllowedSomewhereError;

final class ParamPathCollection
{
    /** @var ParamPath[] */
    public readonly array $paramPaths;

    public function __construct(
        ParamPath ...$paramPaths
    )
    {
        $this->paramPaths = $paramPaths ?: throw new ParamShouldBeAllowedSomewhereError();
    }
}