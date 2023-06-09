<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecification;

final class MultipartBodyParamPath extends ParamPath
{
    public function __construct(
        private readonly string $paramPlacePath,
    )
    {
        parent::__construct($paramPlacePath);
    }

    protected function getParamPlace(): ParamPlace
    {
        return ParamPlace::MultipartBody;
    }

    protected function formatPlacePathToLog(): array
    {
        return ['multipartBodyParamName' => $this->paramPlacePath];
    }

    public function getRouteCondition(): string
    {
        return "request.request.has('" . $this->paramPlacePath . "')";
    }

    public function formatPathToDoc(): string
    {
        return $this->paramPlacePath;
    }
}