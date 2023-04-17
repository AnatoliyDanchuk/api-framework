<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Endpoint\JsonRequestTransformer;

final class JsonBodyParamPath extends ParamPath
{
    public function __construct(
        private readonly array $paramPlacePath,
    )
    {
        parent::__construct($paramPlacePath);
    }

    protected function getParamPlace(): ParamPlace
    {
        return ParamPlace::JsonBody;
    }

    protected function formatPlacePathToLog(): array
    {
        return ['jsonBodyParamPath' => implode(':{', $this->paramPlacePath)];
    }

    public function getRouteCondition(): string
    {
        $attributeName = JsonRequestTransformer::REQUEST_ATTRIBUTE_JSON_CONTENT;
        $jsonItemPath = implode('?.', $this->paramPlacePath);
        return "request.attributes.get('$attributeName')?.$jsonItemPath !== null";
    }
}