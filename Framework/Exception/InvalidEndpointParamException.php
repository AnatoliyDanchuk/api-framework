<?php

namespace Framework\Exception;

use Framework\Endpoint\EndpointInput\EndpointInputInfoBuilder;
use Framework\Endpoint\EndpointInput\FoundInputParam;

final class InvalidEndpointParamException extends ExceptionWithContext
{
    public function __construct(
        FoundInputParam $foundParam,
        ValidatorException $validatorException
    ) {
        parent::__construct([
            'foundParam' => (new EndpointInputInfoBuilder())->buildFilledParamInfo($foundParam),
            'reason' => $validatorException->getContext(),
        ], $validatorException);
    }
}