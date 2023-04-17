<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecification;

final class AppliedParam extends InputParam
{
    private readonly string|array $foundValue;

    public function __construct(
        FoundInputParam $foundInputParam,
        EndpointParamSpecification $paramSpecification
    )
    {
        $this->foundValue = $foundInputParam->value;
        $parsedValue = $paramSpecification->parseValue($foundInputParam->value);
        parent::__construct($foundInputParam->paramPath, $parsedValue);
    }

    public function getFormattedValue(): string|array
    {
        return $this->foundValue;
    }
}