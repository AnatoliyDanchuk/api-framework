<?php

namespace Framework\Exception;

final class ParamValueIsNotFoundAnywhere extends ExceptionWithContext
{
    public function __construct(ParamValueIsNotFound ...$exceptions)
    {
        $combinedContext = [];
        foreach ($exceptions as $exception) {
            $combinedContext[] = $exception->getContext();
        }

        parent::__construct($combinedContext);
    }
}