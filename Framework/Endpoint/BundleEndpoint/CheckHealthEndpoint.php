<?php

namespace Framework\Endpoint\BundleEndpoint;

use Framework\Endpoint\EndpointTemplate\ServiceHttpEndpoint;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Required supporting HTTP GET / with empty body
 * for checking health of webservice by current tool.
 */
final class CheckHealthEndpoint implements ServiceHttpEndpoint
{
    public function getHttpMethod(): string
    {
        return Request::METHOD_GET;
    }

    public function getHttpPath(): string
    {
        return '/';
    }

    public function execute(): Response
    {
        // todo: api auto doc and ui. Maybe use API Platform for Symfony.
        return new Response();
    }
}