<?php

namespace Framework\ExceptionHandler;

use Framework\Endpoint\BundleEndpoint\HelpEndpoint;
use Framework\Exception\InvalidEndpointInputException;
use Framework\ResponseBuilder\InvalidHttpRequestResponseBuilder;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class InvalidEndpointInputHandler implements ExceptionHandlerInterface
{
    private UrlGeneratorInterface $urlGenerator;
    private InvalidHttpRequestResponseBuilder $responseBuilder;
    private HelpEndpoint $helpEndpoint;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        HelpEndpoint $helpEndpoint,
        InvalidHttpRequestResponseBuilder $responseBuilder
    ) {
        $this->helpEndpoint = $helpEndpoint;
        $this->responseBuilder = $responseBuilder;
        $this->urlGenerator = $urlGenerator;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!($exception instanceof InvalidEndpointInputException)) {
            return;
        }
        /** @var InvalidEndpointInputException $exception */

        $documentation = $this->urlGenerator->generate($this->helpEndpoint::class);
        $errorDetails = $exception->getContext();

        $response = $this->responseBuilder->getResponse($errorDetails, $documentation);
        $event->setResponse($response);
    }
}