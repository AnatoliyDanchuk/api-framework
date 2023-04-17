<?php

namespace Framework\ResponseBuilder;

use Symfony\Component\HttpFoundation\Response;

final class InvalidHttpRequestResponseBuilder extends ErrorResponseBuilderTemplate
{
    protected function getHttpCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    protected function getTtl(): int
    {
        // @todo: confirm how we will cache and ignore in common error log
        return 1;
    }

    public function getResponse(array $errorDetails, string $documentation): Response
    {
        return $this->buildResponse($errorDetails, $documentation);
    }
}