<?php

namespace Framework\ResponseBuilder;

use Symfony\Component\HttpFoundation\Response;

abstract class ErrorResponseBuilderTemplate extends ResponseBuilderTemplate
{
    protected function buildResponse(array $errorDetails, ?string $documentation=null): Response
    {
        $errorContext = $this->buildErrorContext($errorDetails);
        $helpContext = $this->buildHelpContext($documentation);
        $responseContext = $errorContext + $helpContext;
        return $this->buildResponseWithContext($responseContext);
    }

    private function buildErrorContext(array $errorDetails): array
    {
        return [
            'error' => $errorDetails,
        ];
    }

    private function buildHelpContext(?string $documentation): array
    {
        return (isset($documentation))
            ? [
                'help' => [
                    'documentation' => $documentation,
                ]
            ]
            : [];
    }
}