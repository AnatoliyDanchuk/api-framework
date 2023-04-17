<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Endpoint\CombinedEndpointParamSpecifications\CombinedEndpointParamSpecification;
use Framework\Endpoint\CombinedEndpointParamSpecifications\ConvertableCombinedEndpointParamSpecification;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecification;
use Framework\Endpoint\EndpointParamSpecification\HasRelatedErrorClass;
use Throwable;

final class ExpectedInput
{
    /** @var EndpointParamSpecification[] */
    private array $allEndpointParams;

    /** @var ConvertableCombinedEndpointParamSpecification[]  */
    private array $endpointCombinedParams;

    /** @var EndpointParamSpecification[] */
    private array $endpointSeparateParams;

    public function __construct(CombinedEndpointParamSpecification|EndpointParamSpecification ...$endpointParams)
    {
        $combinedParams = [];
        $this->endpointCombinedParams = [];
        $this->endpointSeparateParams = [];
        foreach ($endpointParams as $item) {
            if ($item instanceof CombinedEndpointParamSpecification) {
                $this->endpointCombinedParams[] = $item;
                $combinedParams[] = $item->buildContent()->extractAllParamSpecifications();
            } else {
                $this->endpointSeparateParams[] = $item;
            }
        }

        $this->allEndpointParams = array_merge($this->endpointSeparateParams, ...$combinedParams);
    }

    /** @return ConvertableCombinedEndpointParamSpecification[] */
    public function getEndpointCombinedParams(): array
    {
        return $this->endpointCombinedParams;
    }

    /** @return EndpointParamSpecification[] */
    public function getEndpointSeparateParams(): array
    {
        return $this->endpointSeparateParams;
    }

    public function getAllEndpointParams(): array
    {
        return $this->allEndpointParams;
    }

    public function identifyFailedParamsByError(Throwable $error): array
    {
        $params = [];
        foreach ($this->allEndpointParams as $param) {
            if ($param instanceof HasRelatedErrorClass
                && $param->getRelatedErrorClasses()->containsError($error)
            ) {
                $params[] = $param;
            }
        }

        return $params;
    }
}