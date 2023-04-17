<?php

namespace Framework\ExceptionHandler;

use Framework\Exception\FailedEndpointParamError;
use Framework\ResponseBuilder\FailedHttpParamResponseBuilder;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final class FailedEndpointParamHandler implements ExceptionHandlerInterface
{
    private FailedHttpParamResponseBuilder $responseBuilder;

    public function __construct(
        FailedHttpParamResponseBuilder $responseBuilder
    ) {
        $this->responseBuilder = $responseBuilder;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!($exception instanceof FailedEndpointParamError)) {
            return;
        }

        $errorDetails = $exception->getContext();

        $response = $this->responseBuilder->getResponse($errorDetails);
        $event->setResponse($response);
    }
}