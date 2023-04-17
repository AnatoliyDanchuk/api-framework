<?php

namespace Framework\Endpoint\CombinedEndpointParamSpecifications;

use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecification;

final class EndpointParamSpecificationCollection
{
    private array $items;

    public function __construct(
        CombinedEndpointParamSpecification|EndpointParamSpecification ...$paramSpecifications,
    )
    {
        $this->items = $paramSpecifications;
    }

    /** @return EndpointParamSpecification[] */
    public function extractAllParamSpecifications(): array
    {
        $groupedParams = [];
        $separatedParams = [];
        foreach ($this->items as $item) {
            if ($item instanceof CombinedEndpointParamSpecification) {
                $groupedParams[] = $item->buildContent()->extractAllParamSpecifications();
            } else {
                $separatedParams[] = $item;
            }
        }

        return array_merge($separatedParams, ...$groupedParams);
    }

    /**
     * @return CombinedEndpointParamSpecification[]|EndpointParamSpecification[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}