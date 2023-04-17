<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecification;

final class EndpointInputInfoBuilder
{
    public function buildUniqueParamsInfo(array $uniqueParams): array
    {
        return [
            'uniqueParams' => array_map([$this, 'buildParamAvailablePathsInfo'], $uniqueParams),
        ];
    }

    private function buildParamAvailablePathsInfo(EndpointParamSpecification $param): array
    {
        return array_map([$this, 'buildCustomPlacePathInfo'], $param->getAvailableParamPaths()->paramPaths);
    }

    public function buildCustomPlacePathInfo(ParamPath $paramPath): array
    {
        return $paramPath->formatToLog();
    }

    public function buildInputInfo(ParsedInput $parsedInput): array
    {
        return [
            'appliedExpectedParams' => array_map([$this, 'buildFilledParamInfo'], $parsedInput->appliedInput->getWithValues()),
            'unusedPossibleParams' => array_map([$this, 'buildFilledParamInfo'], $parsedInput->appliedInput->getWithoutValues()),
            'ignoredUnexpectedParams' => array_map([$this, 'buildFilledParamInfo'], $parsedInput->ignoredInput->params),
        ];
    }

    public function buildFilledParamInfo(InputParam $param): array
    {
        return $this->buildCustomPlacePathInfo($param->paramPath)
            + $this->buildValueInfo($param->getFormattedValue());
    }

    private function buildValueInfo(mixed $value): array
    {
        return ['value' => $value];
    }
}