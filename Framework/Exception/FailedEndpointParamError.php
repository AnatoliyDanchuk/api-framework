<?php

namespace Framework\Exception;

use Framework\Endpoint\EndpointInput\AppliedParam;
use Framework\Endpoint\EndpointInput\EndpointInputInfoBuilder;

final class FailedEndpointParamError extends ExceptionWithContext
{
    public function __construct(
        ExceptionWithContext $exception,
        AppliedParam ...$appliedParams,
    ) {
        parent::__construct([
            'failedInput' => [
                'relatedParameters' => array_map([new EndpointInputInfoBuilder(), 'buildFilledParamInfo'], $appliedParams),
                'reason' => $exception->getContext()
            ],
        ], $exception);
    }
}