<?php

namespace Framework\Endpoint\BundleEndpoint;

use Framework\Endpoint\EndpointTemplate\ServiceHttpEndpoint;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class HelpEndpoint implements ServiceHttpEndpoint
{
    public function getHttpMethod(): string
    {
        return Request::METHOD_GET;
    }

    public function getHttpPath(): string
    {
        return '/help';
    }

    public function execute(): Response
    {
        return new RedirectResponse('/api/doc.yaml');
    }
}