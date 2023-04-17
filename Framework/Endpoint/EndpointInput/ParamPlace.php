<?php

namespace Framework\Endpoint\EndpointInput;

enum ParamPlace
{
    case UrlQuery;
    case JsonBody;
}
