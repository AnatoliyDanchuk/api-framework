<?php

namespace Framework\Endpoint\EndpointParamSpecification;

use Symfony\Component\Validator\Constraint;

final class ValidatorConstraintCollection
{
    private array $constraints;

    public function __construct(Constraint ...$constraints)
    {
        $this->constraints = $constraints;
    }

    public function toArray(): array
    {
        return $this->constraints;
    }
}