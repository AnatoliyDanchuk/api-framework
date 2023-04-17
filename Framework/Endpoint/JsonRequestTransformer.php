<?php

namespace Framework\Endpoint;

use Framework\Endpoint\EndpointInput\NullSafeObject;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class JsonRequestTransformer
{
    public const REQUEST_ATTRIBUTE_JSON_CONTENT = 'jsonContent';

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        try {
            $json = json_decode($request->getContent(), false, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return;
        }

        $request->attributes->add([self::REQUEST_ATTRIBUTE_JSON_CONTENT => new NullSafeObject($json)]);
    }
}