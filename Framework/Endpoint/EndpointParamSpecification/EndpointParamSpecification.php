<?php

namespace Framework\Endpoint\EndpointParamSpecification;

use Framework\Endpoint\EndpointInput\ParamPathCollection;

interface EndpointParamSpecification extends HasRelatedErrorClass
{
    public function getAvailableParamPaths(): ParamPathCollection;
    public function getExample(): array|object|string|float|int|bool;
    public function getParamConstraints(): ValidatorConstraintCollection;
    public function parseValue(string|array $value);
}