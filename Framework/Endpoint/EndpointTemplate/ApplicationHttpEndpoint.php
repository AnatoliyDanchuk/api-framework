<?php

namespace Framework\Endpoint\EndpointTemplate;

use Framework\Endpoint\EndpointInput\ExpectedInput;
use Framework\Endpoint\EndpointInput\FilledExpectedInput;

interface ApplicationHttpEndpoint extends HttpEndpoint
{
    public function buildExpectedInput(): ExpectedInput;
    public function executeVanguardAction(FilledExpectedInput $input): array;
    public function executePostponedAction(FilledExpectedInput $input): array;
}