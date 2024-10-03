<?php

declare(strict_types=1);

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class RequiredMethod
{
    public function __construct(
        private readonly RequestMethod $method
    ){}

    public function getMethod(): RequestMethod
    {
        return $this->method;
    }

}
