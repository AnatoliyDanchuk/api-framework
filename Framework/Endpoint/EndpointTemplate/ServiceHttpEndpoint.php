<?php

namespace Framework\Endpoint\EndpointTemplate;

use Symfony\Component\HttpFoundation\Response;

interface ServiceHttpEndpoint extends HttpEndpoint
{
    public function execute(): Response;
}