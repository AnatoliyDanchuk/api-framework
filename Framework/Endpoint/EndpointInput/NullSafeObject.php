<?php

namespace Framework\Endpoint\EndpointInput;

final class NullSafeObject
{
    public function __construct(
        private \stdClass $object
    )
    {
    }

    public function __get(string $name)
    {
        $propertyValue = $this->object->$name ?? null;
        return is_object($propertyValue)
            ? new NullSafeObject($propertyValue)
            : $propertyValue;
    }
}