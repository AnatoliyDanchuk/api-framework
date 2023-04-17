<?php

namespace Framework\ResponseBuilder;

use Symfony\Component\HttpFoundation\Response;

final class FailedRequestHandlingResponseBuilder extends ErrorResponseBuilderTemplate
{
    protected function getHttpCode(): int
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    protected function getTtl(): int
    {
        // @todo: confirm to decrease ddos when endpoint can't return success answer.
        return 1;
    }

    public function getResponse(array $errorDetails): Response
    {
        return $this->buildResponse($errorDetails);
    }
}