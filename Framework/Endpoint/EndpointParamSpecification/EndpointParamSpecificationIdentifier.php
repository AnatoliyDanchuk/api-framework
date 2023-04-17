<?php

namespace Framework\Endpoint\EndpointParamSpecification;

final class EndpointParamSpecificationIdentifier
{
    public function array_diff(array $comparedObjects, array ...$arrayOfComparedWithObjects): array
    {
        $diff = [];
        foreach ($comparedObjects as $comparedObject) {
            foreach ($arrayOfComparedWithObjects as $comparedWithObjects) {
                foreach ($comparedWithObjects as $comparedWithObject) {
                    if ($comparedObject::class === $comparedWithObject::class) {
                        continue 3;
                    }
                }
            }

            $diff[] = $comparedObject;
        }
        return $diff;
    }
}