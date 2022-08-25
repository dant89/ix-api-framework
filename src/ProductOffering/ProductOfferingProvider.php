<?php

namespace App\ProductOffering;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\ProductOffering\Service\ProductOfferingService;
use App\ProductOffering\Entity\ProductOffering;
use App\Security\Security;

class ProductOfferingProvider implements
    CollectionDataProviderInterface,
    ItemDataProviderInterface,
    RestrictedDataProviderInterface
{
    protected Security $security;
    protected ProductOfferingService $productOfferingService;

    public function __construct(Security $security, ProductOfferingService $productOfferingService)
    {
        $this->security = $security;
        $this->productOfferingService = $productOfferingService;
    }

    /**
     * @return ProductOffering[]
     * @throws \Exception
     */
    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): array
    {
        return $this->productOfferingService->getProductOfferings($context['filters'] ?? []);
    }

    public function getItem(
        string $resourceClass,
        $id,
        string $operationName = null,
        array $context = []
    ): ?ProductOffering {
        return $this->productOfferingService->getProductOffering($id);
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return ProductOffering::class === $resourceClass;
    }
}
