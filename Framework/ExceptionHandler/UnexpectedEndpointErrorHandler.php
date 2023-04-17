<?php

namespace Framework\ExceptionHandler;

use Framework\Exception\UnexpectedEndpointError;
use Framework\Informer\ExceptionInformer;
use Framework\ResponseBuilder\FailedRequestHandlingResponseBuilder;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelInterface;

final class UnexpectedEndpointErrorHandler implements ExceptionHandlerInterface
{
    private FailedRequestHandlingResponseBuilder $responseBuilder;
    private ExceptionInformer $informer;
    private KernelInterface $kernel;

    public function __construct(
        FailedRequestHandlingResponseBuilder $responseBuilder,
        ExceptionInformer $informer,
        KernelInterface $kernel
    ) {
        $this->kernel = $kernel;
        $this->informer = $informer;
        $this->responseBuilder = $responseBuilder;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!($exception instanceof UnexpectedEndpointError)) {
            return;
        }
        /** @var UnexpectedEndpointError $exception */

        /**
         * For debug on dev enabled another handler.
         * @see Framework/Config/Routes/dev/enableSymfonyDebugError.yaml
         * But this handler included in dev too for more easy test it from local and simplify configs.
         */
        if ($this->kernel->isDebug()) {
            return;
        }

        $this->informer->inform($exception);

        $response = $this->responseBuilder->getResponse([
            'input' => $exception->getInputParams(),
            'endpointError' => 'Sorry, unexpected technical issue happen during endpoint logic running.',
        ]);

        $event->setResponse($response);
    }
}