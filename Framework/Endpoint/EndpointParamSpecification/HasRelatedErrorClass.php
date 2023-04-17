<?php

namespace Framework\Endpoint\EndpointParamSpecification;

interface HasRelatedErrorClass
{
    public function getRelatedErrorClasses(): RelatedErrorClasses;
}