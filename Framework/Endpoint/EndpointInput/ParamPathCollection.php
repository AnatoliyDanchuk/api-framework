<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Exception\ParamShouldBeAllowedSomewhereError;

final class ParamPathCollection
{
    /** @var ParamPath[] */
    public readonly array $paramPaths;

    public function __construct(
        ParamPath ...$paramPaths
    )
    {
        $this->paramPaths = $paramPaths ?: throw new ParamShouldBeAllowedSomewhereError();
    }

    public function searchUrlQueryParamPath(): ?UrlQueryParamPath
    {
        foreach ($this->paramPaths as $paramPath) {
            if ($paramPath instanceof UrlQueryParamPath) {
                return $paramPath;
            }
        }

        return null;
    }

    public function searchMultipartBodyParamPath(): ?MultipartBodyParamPath
    {
        foreach ($this->paramPaths as $paramPath) {
            if ($paramPath instanceof MultipartBodyParamPath) {
                return $paramPath;
            }
        }

        return null;
    }

    public function searchJsonBodyParamPath(): ?JsonBodyParamPath
    {
        foreach ($this->paramPaths as $paramPath) {
            if ($paramPath instanceof JsonBodyParamPath) {
                return $paramPath;
            }
        }

        return null;
    }
}