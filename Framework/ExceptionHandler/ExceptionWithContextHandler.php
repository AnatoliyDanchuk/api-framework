<?php

namespace Framework\ExceptionHandler;

use Framework\Endpoint\EndpointInitializer;
use Framework\Endpoint\EndpointInput\ExpectedInput;
use Framework\Endpoint\EndpointInput\ParsedInput;
use Framework\Exception\FailedEndpointParamError;
use Framework\Exception\UnexpectedEndpointError;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final class ExceptionWithContextHandler implements ExceptionHandlerInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$event->getRequest()->attributes->has(EndpointInitializer::EXPECTED_INPUT_ATTRIBUTE)) {
            return;
        }

        $exception = $event->getThrowable();
        /** @var ExpectedInput $expectedInput */
        $expectedInput = $event->getRequest()->attributes->get(EndpointInitializer::EXPECTED_INPUT_ATTRIBUTE);
        $failedInputParams = $expectedInput->identifyFailedParamsByError($exception);
        if (!empty($failedInputParams)) {
            /** @var ParsedInput $parsedInput */
            $parsedInput = $event->getRequest()->attributes->get(EndpointInitializer::PARSED_INPUT_ATTRIBUTE);

            $event->setThrowable(new FailedEndpointParamError(
                $exception,
                ...$parsedInput->appliedInput->getParams(...$failedInputParams),
            ));
        }

        $event->setThrowable(new UnexpectedEndpointError(
            $event->getRequest()->getMethod(),
            $event->getRequest()->getPathInfo(),
            $event->getRequest()->attributes->get(EndpointInitializer::INPUT_INFO_ATTRIBUTE),
            $exception
        ));
    }
}