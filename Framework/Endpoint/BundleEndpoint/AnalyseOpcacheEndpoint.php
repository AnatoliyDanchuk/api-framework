<?php

namespace Framework\Endpoint\BundleEndpoint;

use Framework\Endpoint\EndpointTemplate\ServiceHttpEndpoint;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AnalyseOpcacheEndpoint implements ServiceHttpEndpoint
{
    public function getHttpMethod(): string
    {
        return Request::METHOD_GET;
    }

    public function getHttpPath(): string
    {
        return '/analyse_opcache';
    }

    public function execute(): Response
    {
        /** @noinspection SpellCheckingInspection */
        $htmlReport = include __DIR__ . "/../../../var/vendor/amnuts/opcache-gui/index.php";
        return new Response($htmlReport);
    }
}