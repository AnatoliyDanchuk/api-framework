<?php

namespace Framework\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ValidatorException extends ExceptionWithContext
{
    /**
     * @param mixed $validatedValue
     */
    public function __construct(
        $validatedValue,
        ConstraintViolationListInterface $violations
    ) {
        $context = [
            'validator' => [
                'validatedValue' => $validatedValue,
                'violations' => (string) $violations,
            ],
        ];

        parent::__construct($context);
    }
}