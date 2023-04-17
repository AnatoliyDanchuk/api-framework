<?php

namespace Framework\ResponseBuilder;

use Framework\JsonCoder\ReadableJsonEncoder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class ResponseBuilderTemplate
{
    protected function buildResponseWithContext($context): Response
    {
        return new JsonResponse(
            (new ReadableJsonEncoder())->encode($context),
            $this->getHttpCode(),
            ['Cache-Control' => 'public, max-age=' . $this->getTtl()],
            true
        );
    }

    abstract protected function getHttpCode(): int;

    abstract protected function getTtl(): int;
}