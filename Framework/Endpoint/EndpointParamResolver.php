<?php

namespace Framework\Endpoint;

use Framework\Endpoint\EndpointInput\FilledExpectedInput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class EndpointParamResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (!is_a($argument->getType(), FilledExpectedInput::class, true)) {
            return [];
        }

        return [$request->attributes->get(EndpointInitializer::FILLED_EXPECTED_INPUT_ATTRIBUTE)];
    }
}