<?php

namespace App\ProductOffering\Service;

use App\ProductOffering\Entity\ProductOffering;
use App\ProductOffering\Repository\ProductOfferingRepository;

class ProductOfferingService
{
    protected ProductOfferingRepository $productOfferingRepository;

    public function __construct(ProductOfferingRepository $productOfferingRepository)
    {
        $this->productOfferingRepository = $productOfferingRepository;
    }

    /**
     * @return ProductOffering[]
     */
    public function getProductOfferings(): array
    {
        return $this->productOfferingRepository->getProductOfferings();
    }

    public function getProductOffering(string $id): ?ProductOffering
    {
        return $this->productOfferingRepository->getProductOffering($id);
    }
}
