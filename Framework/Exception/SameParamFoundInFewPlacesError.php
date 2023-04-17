<?php

namespace Framework\Exception;

use Framework\Endpoint\EndpointInput\EndpointInputInfoBuilder;
use Framework\Endpoint\EndpointInput\FoundInputParam;

final class SameParamFoundInFewPlacesError extends ExceptionWithContext
{
    public function __construct(FoundInputParam ...$foundParams)
    {
        parent::__construct([
            'errorReason' => 'Expects the param found only in 1 place.',
            'foundParams' => array_map([new EndpointInputInfoBuilder(), 'buildFilledParamInfo'], $foundParams),
        ]);
    }
}