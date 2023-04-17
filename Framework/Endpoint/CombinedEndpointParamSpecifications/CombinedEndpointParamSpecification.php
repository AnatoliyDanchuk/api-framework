<?php

namespace Framework\Endpoint\CombinedEndpointParamSpecifications;

interface CombinedEndpointParamSpecification
{
    public function buildContent(): EndpointParamSpecificationCollection;
}