<?php

namespace Framework\Endpoint\EndpointInput;

final class UrlQueryParamPath extends ParamPath
{
    public function __construct(
        private readonly string $paramPlacePath,
    )
    {
        parent::__construct($paramPlacePath);
    }

    protected function getParamPlace(): ParamPlace
    {
        return ParamPlace::UrlQuery;
    }

    protected function formatPlacePathToLog(): array
    {
        return ['urlQueryParamName' => $this->paramPlacePath];
    }

    public function getRouteCondition(): string
    {
        return "request.query.has('" . $this->paramPlacePath . "')";
    }

    public function formatPlacePathToDoc(): string
    {
        return $this->paramPlacePath;
    }
}