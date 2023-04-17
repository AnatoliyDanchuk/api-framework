<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Endpoint\CombinedEndpointParamSpecifications\ConvertableCombinedEndpointParamSpecification;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecification;
use WeakMap;

final class FilledExpectedInput
{
    private WeakMap $params;

    public function __construct(
        WeakMap $params
    )
    {
        $this->params = $params;
    }

    public function getParamValue(EndpointParamSpecification $endpointParam): mixed
    {
        return $this->params[$endpointParam];
    }

    public function getValueOfCombinedParams(ConvertableCombinedEndpointParamSpecification $specifications): mixed
    {
        $groupParamsWithValues = new WeakMap();
        foreach ($specifications->buildContent()->getItems() as $item) {
            if ($item instanceof ConvertableCombinedEndpointParamSpecification) {
                $groupParamsWithValues[$item] = $this->getValueOfCombinedParams($item);
            } else {
                $groupParamsWithValues[$item] = $this->params[$item];
            }
        }

        return $specifications->toApplicationObject(new CombinedEndpointParam($groupParamsWithValues));
    }
}