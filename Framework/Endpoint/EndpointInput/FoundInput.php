<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Exception\ParamValueIsNotFound;

final class FoundInput
{
    private array $indexedParams;

    public function __construct(
        FoundInputParam ...$params
    )
    {
        $this->indexedParams = [];
        foreach ($params as $param) {
            $this->indexedParams[$param->paramPath->getSignature()] = $param;
        }
    }

    public function getParam(ParamPath $paramPath): FoundInputParam
    {
        return $this->indexedParams[$paramPath->getSignature()] ?? throw new ParamValueIsNotFound($paramPath);
    }

    public function diff(FoundInputParam ...$params): array
    {
        /** @see FoundInputParam::__toString() */
        return array_values(array_diff($this->indexedParams, $params));
    }
}