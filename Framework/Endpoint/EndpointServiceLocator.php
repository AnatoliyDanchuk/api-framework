<?php

namespace Framework\Endpoint;

use Framework\Endpoint\CombinedEndpointParamSpecifications\ConvertableCombinedEndpointParamSpecification;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecification;

final class EndpointServiceLocator
{
    private array $services = [];

    public function isSupport(EndpointParamSpecification|string $paramSpecification): bool
    {
        $valueType = $this->getFactoryReturnType($paramSpecification);
        return !is_null($valueType)
            && !$valueType->isBuiltin()
            && (interface_exists($valueType->getName()) || class_exists($valueType->getName()));
    }

    private function getFactoryReturnType(ConvertableCombinedEndpointParamSpecification|EndpointParamSpecification|string $paramSpecification): ?\ReflectionNamedType
    {
        return (new \ReflectionMethod(...$this->getFactoryMethod($paramSpecification)))->getReturnType();
    }

    private function getFactoryMethod(EndpointParamSpecification|ConvertableCombinedEndpointParamSpecification|string $paramSpecification): array
    {
        if (is_a($paramSpecification, EndpointParamSpecification::class, true)) {
            return [$paramSpecification, 'parseValue'];
        }

        if (is_a($paramSpecification, ConvertableCombinedEndpointParamSpecification::class, true)) {
            return [$paramSpecification, 'toApplicationObject'];
        }
    }

    public function register(ConvertableCombinedEndpointParamSpecification|EndpointParamSpecification $paramSpecification, object $object): void
    {
        $this->services[$this->getInterface($paramSpecification)] = $object;
    }

    public function getInterface(ConvertableCombinedEndpointParamSpecification|EndpointParamSpecification|string $paramSpecification): string
    {
        return $this->getFactoryReturnType($paramSpecification)?->getName();
    }

    public function unregisterAll(): void
    {
        $this->services = [];
    }

    public function getService(string $interface): object
    {
        return $this->services[$interface];
    }
}