<?php

namespace Framework\Endpoint\EndpointTemplate;

interface HttpEndpoint
{
    public function getHttpMethod(): string;
    public function getHttpPath(): string;
}