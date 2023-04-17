<?php

namespace Framework\Endpoint;

use Framework\Endpoint\EndpointInput\AppliedInput;
use Framework\Endpoint\EndpointInput\AppliedParam;
use Framework\Endpoint\EndpointInput\EndpointInputInfoBuilder;
use Framework\Endpoint\EndpointInput\ExpectedInput;
use Framework\Endpoint\EndpointInput\FilledExpectedInput;
use Framework\Endpoint\EndpointInput\FoundInput;
use Framework\Endpoint\EndpointInput\FoundInputParam;
use Framework\Endpoint\EndpointInput\IgnoredInput;
use Framework\Endpoint\EndpointInput\JsonBodyParamPath;
use Framework\Endpoint\EndpointInput\ParsedInput;
use Framework\Endpoint\EndpointInput\UrlQueryParamPath;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecification;
use Framework\Endpoint\EndpointTemplate\ApplicationHttpEndpoint;
use Framework\Exception\InvalidEndpointInputException;
use Framework\Exception\InvalidEndpointParamException;
use Framework\Exception\ParamValueIsNotFound;
use Framework\Exception\ParamValueIsNotFoundAnywhere;
use Framework\Exception\SameParamFoundInFewPlacesError;
use Framework\Exception\ValidatorException;
use Framework\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use WeakMap;

final class EndpointInitializer
{
    public const PARSED_INPUT_ATTRIBUTE = 'parsed_input';
    public const INPUT_INFO_ATTRIBUTE = 'input_info';
    public const EXPECTED_INPUT_ATTRIBUTE = 'expected_input';
    public const FILLED_EXPECTED_INPUT_ATTRIBUTE = 'filled_expected_input';

    public function __construct(
        private readonly EndpointServiceLocator $endpointServiceLocator,
        private readonly Validator $validator,
    )
    {
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $callable = $event->getController();
        $isApplicationEndpoint = is_array($callable) && $callable[0] instanceof ApplicationHttpEndpoint;
        if (!$isApplicationEndpoint) {
            return;
        }

        $request = $event->getRequest();
        $expectedInput = $callable[0]->buildExpectedInput();
        $parsedInput = $this->parseRequest($request, $expectedInput);

        $request->attributes->set(self::EXPECTED_INPUT_ATTRIBUTE, $expectedInput);
        $request->attributes->set(self::PARSED_INPUT_ATTRIBUTE, $parsedInput);

        $inputInfo = (new EndpointInputInfoBuilder())->buildInputInfo($parsedInput);
        $request->attributes->set(self::INPUT_INFO_ATTRIBUTE, $inputInfo);

        $filledExpectedInput = $parsedInput->appliedInput->fillExpectedInput($expectedInput);
        $request->attributes->set(self::FILLED_EXPECTED_INPUT_ATTRIBUTE, $filledExpectedInput);
        $this->registerEndpointServices($expectedInput, $filledExpectedInput);
    }

    private function parseRequest(Request $request, ExpectedInput $expectedInput): ParsedInput
    {
        $foundInput = new FoundInput(...array_merge(
            $this->getFoundUrlQueryParams($request),
            $this->getFoundJsonBodyParams($request),
        ));
        return $this->parseInput($foundInput, $expectedInput);
    }

    private function getFoundUrlQueryParams(Request $request): array
    {
        $params = [];

        foreach ($request->query->all() as $paramName => $paramValue) {
            $params[] = new FoundInputParam(
                new UrlQueryParamPath($paramName),
                $paramValue,
            );
        }

        return $params;
    }

    private function getFoundJsonBodyParams(Request $request): array
    {
        $params = [];

        try {
            $foundJson = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            foreach ($this->getAllPaths($foundJson) as $path) {
                $params[] = new FoundInputParam(
                    new JsonBodyParamPath($path),
                    $this->getJsonParamValue($foundJson, $path),
                );
            }
        } catch (\JsonException) {
            //
        }

        return $params;
    }

    private function getAllPaths(\stdClass $jsonObject): array
    {
        $pathItems = [];
        $keys = array_keys(get_object_vars($jsonObject));
        foreach ($keys as $key) {
            $currentPath = [$key];
            if (is_object($jsonObject->$key)) {
                foreach ($this->getAllPaths($jsonObject->$key) as $nextPathItems) {
                    $pathItems[] = array_merge($currentPath, $nextPathItems);
                }
            } else {
                $pathItems[] = $currentPath;
            }
        }

        return $pathItems;
    }

    private function getJsonParamValue(object $jsonItem, array $path): string|array
    {
        foreach ($path as $key) {
            $jsonItem = $jsonItem->$key;
        }
        return $jsonItem;
    }

    private function parseInput(FoundInput $foundInput, ExpectedInput $expectedInput): ParsedInput
    {
        $appliedParams = new WeakMap();
        $expectedFoundParams = [];
        $invalidParamExceptions = [];

        foreach ($expectedInput->getAllEndpointParams() as $paramSpecification) {
            $foundParam = $this->getFoundParam($paramSpecification, $foundInput);
            try {
                $appliedParams[$paramSpecification] = $this->buildAppliedParam($paramSpecification, $foundParam);
            } catch (InvalidEndpointParamException $exception) {
                $invalidParamExceptions[] = $exception;
            }
            $expectedFoundParams[] = $foundParam;
        }

        if (!empty($invalidParamExceptions)) {
            throw new InvalidEndpointInputException(...$invalidParamExceptions);
        }

        return new ParsedInput(
            new AppliedInput($appliedParams),
            new IgnoredInput(...$foundInput->diff(...$expectedFoundParams)),
        );
    }

    private function getFoundParam(
        EndpointParamSpecification $paramSpecification,
        FoundInput $foundInput,
    ): FoundInputParam
    {
        $foundParams = [];
        $notFoundExceptions = [];

        foreach ($paramSpecification->getAvailableParamPaths()->paramPaths as $paramPath) {
            try {
                $foundParams[] = $foundInput->getParam($paramPath);
            } catch (ParamValueIsNotFound $exception) {
                $notFoundExceptions[] = $exception;
            }
        }

        switch (count($foundParams)) {
            case 0: throw new ParamValueIsNotFoundAnywhere(...$notFoundExceptions);
            case 1: return $foundParams[0];
            default: throw new SameParamFoundInFewPlacesError(...$foundParams);
        }
    }

    private function buildAppliedParam(
        EndpointParamSpecification $paramSpecification,
        FoundInputParam $foundInputParam,
    ): AppliedParam
    {
        try {
            $this->validator->validate($foundInputParam->value, $paramSpecification->getParamConstraints());
        } catch (ValidatorException $exception) {
            throw new InvalidEndpointParamException($foundInputParam, $exception);
        }
        return new AppliedParam(
            $foundInputParam,
            $paramSpecification,
        );
    }

    private function registerEndpointServices(ExpectedInput $expectedInput, FilledExpectedInput $endpointSpecifiedInput): void
    {
        $this->endpointServiceLocator->unregisterAll();

        foreach ($expectedInput->getEndpointCombinedParams() as $endpointCombinedParam) {
            $this->endpointServiceLocator->register($endpointCombinedParam, $endpointSpecifiedInput->getValueOfCombinedParams($endpointCombinedParam));
        }

        foreach ($expectedInput->getEndpointSeparateParams() as $endpointSeparateParam) {
            if ($this->endpointServiceLocator->isSupport($endpointSeparateParam)) {
                $this->endpointServiceLocator->register($endpointSeparateParam, $endpointSpecifiedInput->getParamValue($endpointSeparateParam));
            }
        }
    }
}