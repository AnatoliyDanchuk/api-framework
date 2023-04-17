<?php

namespace Framework\Config\Routes;

use FilesystemIterator;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationIdentifier;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecification;
use Framework\Endpoint\EndpointTemplate\ApplicationHttpEndpoint;
use Framework\Endpoint\EndpointTemplate\HttpEndpoint;
use Framework\Endpoint\EndpointTemplate\ServiceHttpEndpoint;
use LogicException;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class HttpEndpointLoader extends FileLoader
{
    public const DEFER_RUN = 'defer';

    public function __construct(
        private readonly ContainerInterface $serviceProvider,
        private readonly EndpointParamSpecificationIdentifier $endpointParamSpecificationIdentifier,
        FileLocator $locator,
    ) {
        parent::__construct($locator);
    }

    public function supports($resource, string $type = null): bool
    {
        return $type === HttpEndpoint::class;
    }

    public function load($resource, string $type = null): RouteCollection
    {
        $routes = $this->loadAllRoutes($resource);
        $this->fixRoutesWithSamePath($routes);

        return $routes;
    }

    public function loadAllRoutes($resource): RouteCollection
    {
        $routes = new RouteCollection();

        $path = $this->locator->locate($resource);
        if (is_dir($path)) {
            $endpointDirectory = new FilesystemIterator($path, FilesystemIterator::CURRENT_AS_PATHNAME);
            foreach ($endpointDirectory as $endpointFile) {
                $directoryRoutes = $this->loadAllRoutes($endpointFile);
                $routes->addCollection($directoryRoutes);
            }
        } else {
            $class = $this->getFirstFullClassName($path);

            $classIsNotTemplate = (new ReflectionClass($class))->isFinal();
            $classExtendedEndpointTemplate = is_subclass_of($class, HttpEndpoint::class);
            if ($classExtendedEndpointTemplate && $classIsNotTemplate) {
                $endpoint = $this->serviceProvider->get($class);
                if ($endpoint instanceof ApplicationHttpEndpoint) {
                    [, $method] = [$endpoint, 'executePostponedAction'];
                    $routes->add($endpoint::class, new Route(
                        $endpoint->getHttpPath(),
                        ['_controller' => [$endpoint::class, $method]],
                        methods: [$endpoint->getHttpMethod()],
                    ));
                    [, $method] = [$endpoint, 'executeVanguardAction'];
                    $routes->add('defer_' . $endpoint::class, new Route(
                        '/' . self::DEFER_RUN . $endpoint->getHttpPath(),
                        ['_controller' => [$endpoint::class, $method]],
                        methods: [$endpoint->getHttpMethod()],
                    ));
                } elseif ($endpoint instanceof ServiceHttpEndpoint) {
                    [, $method] = [$endpoint, 'execute'];
                    $routes->add($endpoint::class, new Route(
                        $endpoint->getHttpPath(),
                        ['_controller' => [$endpoint::class, $method]],
                        methods: [$endpoint->getHttpMethod()],
                    ));
                }
            }
            gc_mem_caches();
        }

        return $routes;
    }

    protected function getFirstFullClassName(string $file): string
    {
        $class = false;
        $namespace = false;
        $tokens = token_get_all(file_get_contents($file));

        $nsTokens = [T_NS_SEPARATOR => true, T_STRING => true];
        if (defined('T_NAME_QUALIFIED')) {
            $nsTokens[T_NAME_QUALIFIED] = true;
        }
        for ($i = 0; isset($tokens[$i]); ++$i) {
            $token = $tokens[$i];
            if (!isset($token[1])) {
                continue;
            }

            if (true === $class && T_STRING === $token[0]) {
                $firstFullClassName = $namespace.'\\'.$token[1];
                break;
            }

            if (true === $namespace && isset($nsTokens[$token[0]])) {
                $namespace = $token[1];
                $token = $tokens[++$i];
            }

            if (T_CLASS === $token[0]) {
                $class = true;
            }

            if (T_NAMESPACE === $token[0]) {
                $namespace = true;
            }
        }

        return $firstFullClassName ?? throw new LogicException("File $file is not correct HttpEndpoint.");
    }

    private function fixRoutesWithSamePath(RouteCollection $routes): void
    {
        foreach ($this->getRouteCollectionsBySamePath($routes) as $path => $routeCollectionWithSamePath) {
            $expectedParamsByRouteName = $this->indexExpectedParamsByRoutePath($routeCollectionWithSamePath);

            $routeNamesWithoutUniqueParams = [];
            foreach ($expectedParamsByRouteName as $routeName => $expectedParams) {
                $expectedParamsOfOtherRoutes = array_diff_key($expectedParamsByRouteName, [$routeName => null]);
                $uniqueExpectedParams = $this->endpointParamSpecificationIdentifier->array_diff(
                    $expectedParams,
                    ...array_values($expectedParamsOfOtherRoutes)
                );

                if (!empty($uniqueExpectedParams)) {
                    $condition = $this->buildRouteCondition(...$uniqueExpectedParams);
                    /** @var Route $route */
                    $route = $routes->get($routeName);
                    $route->setCondition($condition);
                } else {
                    $routeNamesWithoutUniqueParams[] = $routeName;
                }
            }

            if (!empty($routeNamesWithoutUniqueParams)) {
                // Simplify matching routes in tests.
                sort($routeNamesWithoutUniqueParams);

                switch (count($routeNamesWithoutUniqueParams)) {
                    case 1:
                        throw new LogicException("Route $routeNamesWithoutUniqueParams[0] has not unique signature");
                    default:
                        throw new LogicException("Path $path has routes without unique signature."
                        . ' Related routes: ' . implode(', ', $routeNamesWithoutUniqueParams) . '.'
                    );
                }
            }
        }
    }

    /**
     * @return RouteCollection[]
     */
    private function getRouteCollectionsBySamePath(RouteCollection $routes): array
    {
        $routeCollectionsByPath = [];
        foreach ($routes->all() as $routeName => $route) {
            ($routeCollectionsByPath[$route->getPath()] ??= new RouteCollection())->add($routeName, $route);
        }
        return array_filter($routeCollectionsByPath, static function (RouteCollection $routeCollection) {
            return $routeCollection->count() > 1;
        });
    }

    /**
     * @return EndpointParamSpecification[][]
     */
    private function indexExpectedParamsByRoutePath(RouteCollection $routeCollection): array
    {
        $expectedParamsByRoutePath = [];
        foreach ($routeCollection as $routePath => $route) {
            $endpointClass = $route->getDefault('_controller')[0];
            /** @var ApplicationHttpEndpoint $endpoint */
            $endpoint = $this->serviceProvider->get($endpointClass);
            $expectedParamsByRoutePath[$routePath] = $endpoint->buildExpectedInput()->getAllEndpointParams();
        }
        return $expectedParamsByRoutePath;
    }

    private function buildRouteCondition(EndpointParamSpecification ...$params): string
    {
        $conditionChecks = [];
        foreach ($params as $param) {
            foreach ($param->getAvailableParamPaths()->paramPaths as $paramPath) {
                $conditionChecks[] = $paramPath->getRouteCondition();
            }
        }

        return implode(" || ", $conditionChecks);
    }
}
