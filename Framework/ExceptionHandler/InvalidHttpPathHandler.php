<?php

namespace Framework\ExceptionHandler;

use Framework\Endpoint\BundleEndpoint\CheckHealthEndpoint;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationIdentifier;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecification;
use Framework\Endpoint\EndpointTemplate\ApplicationHttpEndpoint;
use Framework\Endpoint\EndpointInput\EndpointInputInfoBuilder;
use Framework\ResponseBuilder\InvalidHttpRequestResponseBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

final class InvalidHttpPathHandler implements ExceptionHandlerInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CheckHealthEndpoint $helpEndpoint,
        private readonly InvalidHttpRequestResponseBuilder $responseBuilder,
        private readonly RouterInterface $router,
        private readonly ContainerInterface $serviceProvider,
        private readonly EndpointParamSpecificationIdentifier $endpointParamSpecificationIdentifier,
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!($exception instanceof NotFoundHttpException)) {
            return;
        }
        /** @var NotFoundHttpException $exception */

        $matchedRouteCollectionByPath = $this->getMatchedRouteCollectionByPath($event);
        if ($matchedRouteCollectionByPath->count() > 2) {
            $documentation = '';
            $errorDetails = [
                'reason' => 'Request has not enough signature for binding it to one of found routes.',
                'explanation' => 'Found few routes for your request',
                'expectation' => 'Add at least one of unique params to request'
                    . ' for binding request to expected route.',
                'router' => [
                    'foundRelatedRoutes' => $this->buildFoundRelatedRoutes($matchedRouteCollectionByPath),
                    'violation' => $exception->getMessage(),
                ],
            ];
        } else {
            $documentation = $this->urlGenerator->generate($this->helpEndpoint::class);
            $errorDetails = [
                'invalidHttpPath' => [
                    'violation' => $exception->getMessage(),
                ],
            ];
        }

        $response = $this->responseBuilder->getResponse($errorDetails, $documentation);
        $event->setResponse($response);
    }

    private function getMatchedRouteCollectionByPath(ExceptionEvent $event): RouteCollection
    {
        $matchedRouteCollectionByPath = new RouteCollection();
        $requestPath = $event->getRequest()->getPathInfo();
        foreach ($this->router->getRouteCollection() as $routeName => $route) {
            if ($route->getPath() === $requestPath) {
                $matchedRouteCollectionByPath->add($routeName, $route);
            }
        }
        return $matchedRouteCollectionByPath;
    }

    private function buildFoundRelatedRoutes(RouteCollection $matchedRouteCollectionByPath): array
    {
        $expectedParamsByRouteName = array_map([$this, 'getEndpointParams'], $matchedRouteCollectionByPath->all());
        $uniqueParamsByRouteName = $this->getUniqueParamsByRouteName($expectedParamsByRouteName);
        return array_map([new EndpointInputInfoBuilder(), 'buildUniqueParamsInfo'], $uniqueParamsByRouteName);
    }

    /**
     * @return EndpointParamSpecification[]
     */
    private function getEndpointParams(Route $route): array
    {
        $endpointClass = $route->getDefault('_controller')[0];
        /** @var ApplicationHttpEndpoint $endpoint */
        $endpoint = $this->serviceProvider->get($endpointClass);
        return $endpoint->buildExpectedInput()->getAllEndpointParams();
    }

    /** @return EndpointParamSpecification[][] */
    private function getUniqueParamsByRouteName(array $expectedParamsByRouteName): array
    {
        $uniqueParamsByRouteName = [];
        foreach ($expectedParamsByRouteName as $routeName => $expectedParams) {
            $others = array_diff_key($expectedParamsByRouteName, [$routeName => null]);
            $uniqueParamsByRouteName[$routeName] = $this->endpointParamSpecificationIdentifier->array_diff(
                $expectedParams,
                ...array_values($others)
            );
        }
        return $uniqueParamsByRouteName;
    }
}