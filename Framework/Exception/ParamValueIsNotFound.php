<?php

namespace Framework\Exception;

use Framework\Endpoint\EndpointInput\EndpointInputInfoBuilder;
use Framework\Endpoint\EndpointInput\ParamPath;

final class ParamValueIsNotFound extends ExceptionWithContext
{
    public function __construct(ParamPath $paramPath)
    {
        parent::__construct([
            'errorReason' => 'Param value is not found.',
        ] + (new EndpointInputInfoBuilder())->buildCustomPlacePathInfo($paramPath));
    }
}