<?php

declare(strict_types=1);

#[Attribute(Attribute::TARGET_METHOD)]
final class AllowedMethods
{
    public function __construct(
        /** @var RequestMethod[] */
        private readonly array $methods
    ){}

    /**
     * @return RequestMethod[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }
}
