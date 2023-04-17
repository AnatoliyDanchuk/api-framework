<?php

namespace Framework;

use Framework\Endpoint\EndpointParamSpecification\ValidatorConstraintCollection;
use Framework\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Validator
{
    private ValidatorInterface $validator;

    public function __construct(
        ValidatorInterface $validator
    ) {
        $this->validator = $validator;
    }

    public function validate($value, ValidatorConstraintCollection $validatorConstraintCollection): void
    {
        $violations = $this->validator->validate($value, $validatorConstraintCollection->toArray());

        if ($violations->count() > 0) {
            throw new ValidatorException($value, $violations);
        }
    }
}