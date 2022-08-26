<?php

namespace App\OpenApi;

use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\OpenApi;

abstract class OpenApiDecorator implements OpenApiFactoryInterface
{
    protected OpenApiFactoryInterface $decorated;
    protected array $tags = [];

    public function __construct(OpenApiFactoryInterface $decorated, ?array $tags = [])
    {
        $this->decorated = $decorated;
        $this->tags = $tags;
    }

    public function __invoke(array $context = []): OpenApi
    {
        return ($this->decorated)($context);
    }
}
