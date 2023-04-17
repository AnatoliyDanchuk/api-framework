<?php

namespace Framework\Exception;

final class InvalidEndpointInputException extends ExceptionWithContext
{
    public function __construct(
        InvalidEndpointParamException ...$invalidParamExceptions
    ) {
        $context = [];
        foreach ($invalidParamExceptions as $exception) {
            $context[] = $exception->getContext();
        }

        parent::__construct([
            'invalidInput' => $context,
        ]);
    }
}