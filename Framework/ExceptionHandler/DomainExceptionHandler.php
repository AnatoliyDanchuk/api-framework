<?php

namespace Framework\ExceptionHandler;

use Domain\Exception\DomainException;
use Framework\Endpoint\EndpointInitializer;
use Framework\ResponseBuilder\ExceptionResponseBuilder;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final class DomainExceptionHandler implements ExceptionHandlerInterface
{
    private ExceptionResponseBuilder $responseBuilder;

    public function __construct(
        ExceptionResponseBuilder $responseBuilder
    ) {
        $this->responseBuilder = $responseBuilder;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!($exception instanceof DomainException)) {
            return;
        }

        $inputInfo = $event->getRequest()->attributes->get(EndpointInitializer::INPUT_INFO_ATTRIBUTE);
        $response = $this->responseBuilder->getResponse($exception, $inputInfo);

        $event->setResponse($response);
    }
}