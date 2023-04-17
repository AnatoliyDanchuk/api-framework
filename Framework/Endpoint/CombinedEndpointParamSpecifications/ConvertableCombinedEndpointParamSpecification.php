<?php

namespace Framework\Endpoint\CombinedEndpointParamSpecifications;

use Framework\Endpoint\EndpointInput\CombinedEndpointParam;

interface ConvertableCombinedEndpointParamSpecification
    extends CombinedEndpointParamSpecification
{
    public function toApplicationObject(CombinedEndpointParam $combinedEndpointParam): object;
}