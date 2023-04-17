<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecification;
use WeakMap;

final class AppliedInput
{
    /** @var WeakMap|AppliedParam[] */
    private WeakMap $params;

    public function __construct(
        WeakMap $params
    )
    {
        $this->params = $params;
    }

    public function fillExpectedInput(ExpectedInput $specifications): FilledExpectedInput
    {
        $expectedInputWithValues = new WeakMap();
        foreach ($specifications->getAllEndpointParams() as $paramSpecification) {
            $expectedInputWithValues[$paramSpecification] = $this->params[$paramSpecification]->value;
        }

        return new FilledExpectedInput($expectedInputWithValues);
    }

    /** @return AppliedParam[] */
    public function getParams(EndpointParamSpecification ...$paramSpecifications): array
    {
        $params = [];
        foreach ($paramSpecifications as $paramSpecification) {
            $params[] = $this->params[$paramSpecification];
        }
        return $params;
    }

    /** @return AppliedParam[] */
    public function getWithoutValues(): array
    {
        $params = [];
        foreach ($this->params as $param) {
            if (!$this->isParamHasValue($param)) {
                $params[] = $param;
            }
        }
        return $params;
    }

    /** @return AppliedParam[] */
    public function getWithValues(): array
    {
        $params = [];
        foreach ($this->params as $param) {
            if ($this->isParamHasValue($param)) {
                $params[] = $param;
            }
        }
        return $params;
    }

    private function isParamHasValue(AppliedParam $param): bool
    {
        return $param->value !== null
            && $param->value !== ''
            && (!is_array($param->value) || !empty($param->value));
    }
}