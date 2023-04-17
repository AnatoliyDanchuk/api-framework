<?php

namespace Framework\Exception;

final class ParamShouldBeAllowedSomewhereError extends ExceptionWithContext
{
    public function __construct()
    {
        parent::__construct([
            'errorReason' => 'Expects at least one allowed place for param.',
        ]);
    }
}