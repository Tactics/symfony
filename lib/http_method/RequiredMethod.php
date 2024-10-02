<?php

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RequiredMethod
{
    public function __construct(
        private readonly RequestMethod $method
    ){}

    public function getMethod(): RequestMethod
    {
        return $this->method;
    }

}
