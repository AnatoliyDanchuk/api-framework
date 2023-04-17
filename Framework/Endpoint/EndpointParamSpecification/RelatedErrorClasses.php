<?php

namespace Framework\Endpoint\EndpointParamSpecification;

use Framework\Exception\ExceptionWithContext;
use Throwable;
use TypeError;

final class RelatedErrorClasses
{
    private array $errors;

    public function __construct(string ...$errors)
    {
        foreach ($errors as $error) {
            if (!is_a($error, ExceptionWithContext::class, true)) {
                throw new TypeError("$error is not compatible with " . ExceptionWithContext::class);
            }
        }
        $this->errors = $errors;
    }

    public function containsError(Throwable $error): bool
    {
        return in_array(get_class($error), $this->errors, true);
    }
}