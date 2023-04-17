<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Endpoint\CombinedEndpointParamSpecifications\ConvertableCombinedEndpointParamSpecification;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecification;
use WeakMap;

final class CombinedEndpointParam
{
    private WeakMap $params;

    public function __construct(
        WeakMap $params
    )
    {
        $this->params = $params;
    }

    public function getValue(
        EndpointParamSpecification|ConvertableCombinedEndpointParamSpecification $endpointParam
    ): mixed
    {
        return $this->params[$endpointParam];
    }
}