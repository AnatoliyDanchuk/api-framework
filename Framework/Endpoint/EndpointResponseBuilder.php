<?php

namespace Framework\Endpoint;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ViewEvent;

final class EndpointResponseBuilder
{
    public function onKernelView(ViewEvent $event): void
    {
        $event->setResponse(new JsonResponse([
            'input' => $event->getRequest()->attributes->get(EndpointInitializer::INPUT_INFO_ATTRIBUTE),
            'output' => $event->getControllerResult(),
        ]));
    }
}