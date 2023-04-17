<?php

namespace Framework\Endpoint\EndpointInput;

final class ParsedInput
{
    public function __construct(
        public readonly AppliedInput $appliedInput,
        public readonly IgnoredInput $ignoredInput,
    )
    {
    }
}