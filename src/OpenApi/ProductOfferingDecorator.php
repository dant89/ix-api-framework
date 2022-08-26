<?php

namespace App\OpenApi;

use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\OpenApi;
use ApiPlatform\Core\OpenApi\Model;

final class ProductOfferingDecorator extends OpenApiDecorator
{
    private const URI_PATH = '/api/v2/product-offerings';

    public function __construct(OpenApiFactoryInterface $decorated)
    {
        parent::__construct($decorated);
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = parent::__invoke($context);
        $this->addFilter($openApi);
        return $openApi;
    }

    private function addFilter(OpenApi $openApi): void
    {
        $pathItem = $openApi->getPaths()->getPath(self::URI_PATH);
        $operation = $pathItem->getGet();

        $openApi->getPaths()->addPath(self::URI_PATH, $pathItem->withGet(
            $operation->withParameters(array_merge(
                $operation->getParameters(),
                [new Model\Parameter('id', 'query', 'Filter by id.')],
                [new Model\Parameter('type', 'query', 'Filter by type.')],
                [new Model\Parameter('name', 'query', 'Filter by name.')],
                [new Model\Parameter('handover_metro_area', 'query', 'Filter by handover_metro_area.')],
                [new Model\Parameter('handover_metro_area_network', 'query', 'Filter by handover_metro_area_network.')],
                [new Model\Parameter('service_metro_area', 'query', 'Filter by service_metro_area.')],
                [new Model\Parameter('service_metro_area_network', 'query', 'Filter by service_metro_area_network.')],
                [new Model\Parameter('service_provider', 'query', 'Filter by service_provider.')],
                [new Model\Parameter('downgrade_allowed', 'query', 'Filter by downgrade_allowed.')],
                [new Model\Parameter('upgrade_allowed', 'query', 'Filter by upgrade_allowed.')],
                [new Model\Parameter('bandwidth', 'query', 'Filter by bandwidth.')],
                [new Model\Parameter('physical_port_speed', 'query', 'Filter by physical_port_speed.')],
                [new Model\Parameter('service_provider_region', 'query', 'Filter by service_provider_region.')],
                [new Model\Parameter('service_provider_pop', 'query', 'Filter by service_provider_pop.')],
                [new Model\Parameter('delivery_method', 'query', 'Filter by delivery_method.')],
                [new Model\Parameter('cloud_key', 'query', 'Filter by cloud_key.')],
                [new Model\Parameter('fields', 'query', 'Filter by fields.')],
            ))
        ));
    }
}
