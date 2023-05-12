<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecification;
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

    public function formatPathToDoc(EndpointParamSpecification $endpointParamSpecification): array
    {
        $tree = [];
        $treeCursor = &$tree;
        $path = $this->paramPlacePath;
        $lastPathItem = array_pop($path);
        foreach ($path as $item) {
            $treeCursor[$item] ??= [];
            $treeCursor = &$treeCursor[$item];
        }
        $treeCursor[$lastPathItem] = $endpointParamSpecification;
        return $tree;
    }
}