<?php

namespace Framework\Config\Routes;

use Framework\Endpoint\CombinedEndpointParamSpecifications\CombinedEndpointParamSpecification;
use Framework\Endpoint\EndpointInput\JsonBodyParamPath;
use Framework\Endpoint\EndpointInput\MultipartBodyParamPath;
use Framework\Endpoint\EndpointInput\UrlQueryParamPath;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecification;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberInterface;
use OpenApi\Annotations\Items;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\JsonContent;
use OpenApi\Annotations\Parameter;
use OpenApi\Annotations\PathItem;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\RequestBody;
use Symfony\Component\Routing\Route;
use Symfony\Component\Validator\Constraints;

final class HttpEndpointDescriber implements RouteDescriberInterface
{
    private const HTTP_METHOD_MAP = [
        \Symfony\Component\HttpFoundation\Request::METHOD_GET => \OpenApi\Annotations\Get::class,
        \Symfony\Component\HttpFoundation\Request::METHOD_POST => \OpenApi\Annotations\Post::class,
        \Symfony\Component\HttpFoundation\Request::METHOD_PUT => \OpenApi\Annotations\Put::class,
        \Symfony\Component\HttpFoundation\Request::METHOD_PATCH => \OpenApi\Annotations\Patch::class,
        \Symfony\Component\HttpFoundation\Request::METHOD_DELETE => \OpenApi\Annotations\Delete::class,
        \Symfony\Component\HttpFoundation\Request::METHOD_OPTIONS => \OpenApi\Annotations\Options::class,
        \Symfony\Component\HttpFoundation\Request::METHOD_HEAD => \OpenApi\Annotations\Head::class,
    ];

    public function describe(OpenApi $api, Route $route, \ReflectionMethod $reflectionMethod): void
    {
        $pathItem = current(array_filter($api->paths, function (PathItem $pathItem) use ($route): bool {
            return $pathItem->path === $route->getPath();
        }));

        foreach ($route->getMethods() as $httpMethod) {
            $operation = Util::getChild($pathItem, self::HTTP_METHOD_MAP[$httpMethod]);
            $allParams = $this->getAllParams($route);

            if ($paramsWithUrlQueryPath = $allParams[UrlQueryParamPath::class] ?? []) {
                $this->describeParameters($operation, 'query', ...$paramsWithUrlQueryPath);
            }

            if ($paramsWithJsonBodyPath = $allParams[JsonBodyParamPath::class] ?? []) {
                $this->describeJsonRequestBody($operation, ...$paramsWithJsonBodyPath);
            } elseif ($paramsWithMultipartBodyPath = $allParams[MultipartBodyParamPath::class] ?? []) {
                $this->describeParameters($operation, 'body', ...$paramsWithMultipartBodyPath);
            }
        }
    }

    private function getAllParams(Route $route): array
    {
        [$endpointClass] = $route->getDefault('_controller');
        $reflectionEndpointClass = new \ReflectionClass($endpointClass);

        return $this->getParams($reflectionEndpointClass);
    }

    private function getParams(\ReflectionClass $reflectionParentClass): array
    {
        $allParams = [];

        foreach ($reflectionParentClass->getConstructor()->getParameters() as $parameter) {
            $parameterClass = $parameter->getClass()->getName();
            $reflectionParamClass = new \ReflectionClass($parameterClass);

            if (is_subclass_of($parameterClass, CombinedEndpointParamSpecification::class)) {
                $allParams = array_merge_recursive($allParams, $this->getParams($reflectionParamClass));
            }

            if (is_subclass_of($parameterClass, EndpointParamSpecification::class)) {
                /** @var EndpointParamSpecification $param */
                $param = $reflectionParamClass->newInstanceWithoutConstructor();
                if ($paramPath = $param->getAvailableParamPaths()->searchUrlQueryParamPath()) {
                    $allParams[UrlQueryParamPath::class][] = [$paramPath, $param];
                } elseif($paramPath = $param->getAvailableParamPaths()->searchJsonBodyParamPath()) {
                    $allParams[JsonBodyParamPath::class][] = [$paramPath, $param];
                } elseif($paramPath = $param->getAvailableParamPaths()->searchMultipartBodyParamPath()) {
                    $allParams[MultipartBodyParamPath::class][] = [$paramPath, $param];
                }
            }
        }
        return $allParams;
    }

    private function describeParameters(\OpenApi\Annotations\AbstractAnnotation $operation, string $in, array ...$paramsWithPath): void
    {
        $describedUrlQueryParams = [];
        foreach ($this->buildTreeOfParams(...$paramsWithPath) as $name => $param) {
            $describedUrlQueryParams[] = Util::createChild($operation, Parameter::class, [
                'in' => $in,
                'name' => $name,
                'required' => $this->isRequiredParam($param),
                'example' => $param->getExample(),
            ]);
        }
        $operation->merge($describedUrlQueryParams);
    }

    private function isRequiredParam(EndpointParamSpecification $value): bool
    {
        return !empty(array_filter($value->getParamConstraints()->toArray(), function ($constraint) {
            return $constraint instanceof Constraints\NotBlank;
        }));
    }

    private function buildTreeOfParams(array ...$paramsWithPath): array
    {
        $paramsByPath = [];
        foreach ($paramsWithPath as [$paramPath, $param]) {
            $formattedPath = $paramPath->formatPathToDoc();
            if (is_string($formattedPath)) {
                $paramByPath = [$formattedPath => $param];
            } else {
                $paramByPath = [];
                $treeCursor = &$paramByPath;
                $path = $formattedPath;
                $lastPathItem = array_pop($path);
                foreach ($path as $item) {
                    $treeCursor[$item] ??= [];
                    $treeCursor = &$treeCursor[$item];
                }
                $treeCursor[$lastPathItem] = $param;
            }

            $paramsByPath[] = $paramByPath;
        }

        return array_merge_recursive(...$paramsByPath);
    }

    private function describeJsonRequestBody(\OpenApi\Annotations\AbstractAnnotation $operation, array ...$paramsWithJsonBodyPath): void
    {
        $paramTree = $this->buildTreeOfParams(...$paramsWithJsonBodyPath);
        $requestBody = Util::getChild($operation, RequestBody::class);
        /**
         * getChild is not possible because it does not support inherited classes like JsonContent
         * @see RequestBody::$_nested
         */
        $content = Util::createChild($requestBody, JsonContent::class, [
            'required' => $this->getRequiredJsonChildren($paramTree),
        ]);
        $requestBody->merge([$content]);

        $this->describeJsonParamTree($paramTree, $content);
    }

    private function describeJsonParamTree(array $tree, \OpenApi\Annotations\AbstractAnnotation $parentItem): void
    {
        $properties = [];
        foreach ($tree as $treeKey => $treeValue) {
            if (is_array($treeValue)) {
                $propertyAsObject = Util::createChild($parentItem, Property::class, [
                    'required' => $this->getRequiredJsonChildren($treeValue),
                    'property' => $treeKey,
                    'type' => 'object',
                ]);
                $this->describeJsonParamTree($treeValue, $propertyAsObject);
                $properties[] = $propertyAsObject;
            } elseif($treeValue instanceof EndpointParamSpecification) {
                $paramExample = $treeValue->getExample();
                $propertyAsValue = $this->describeParamValue($paramExample, $parentItem, $treeKey);
                $properties[] = $propertyAsValue;
            }
        }

        $parentItem->merge($properties);
    }

    private function getRequiredJsonChildren(array $jsonParamTree): array
    {
        $requiredChildren = [];
        foreach ($jsonParamTree as $key => $value) {
            if ($value instanceof EndpointParamSpecification && !$this->isRequiredParam($value)) {
                continue;
            }

            $requiredChildren[] = $key;
        }
        return $requiredChildren;
    }

    private function describeParamValue($paramExample, \OpenApi\Annotations\AbstractAnnotation $parentItem, ?string $treeKey = null): \OpenApi\Annotations\AbstractAnnotation
    {
        $childPropertyNameSection = $treeKey ? ['property' => $treeKey] : [];
        $childClass = $treeKey ? Property::class : Items::class;

        if (is_array($paramExample)) {
            $propertyAsValue = Util::createChild($parentItem, $childClass, $childPropertyNameSection + [
                    'type' => 'array',
                    'example' => $paramExample,
                ]);

            // @reason: only 1 item can be applied. ApiDoc expected array has strong typed structure.
            $paramExampleItem = current($paramExample);
            $items = [
                $this->describeParamValue($paramExampleItem, $propertyAsValue),
            ];
            $propertyAsValue->merge($items);
        } elseif (is_object($paramExample)) {
            $propertyAsValue = Util::createChild($parentItem, $childClass, $childPropertyNameSection + [
                    'required' => array_keys(get_object_vars($paramExample)),
                    'type' => 'object',
                ]);

            $itemProperties = [];
            foreach (get_object_vars($paramExample) as $key => $itemProperty) {
                $itemProperties[] = $this->describeParamValue($itemProperty, $propertyAsValue, $key);
            }
            $propertyAsValue->merge($itemProperties);
        } else {
            $propertyAsValue = Util::createChild($parentItem, $childClass, $childPropertyNameSection + [
                    'type' => gettype($paramExample),
                    'example' => $paramExample,
                ]);
        }

        return $propertyAsValue;
    }
}