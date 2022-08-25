<?php

namespace App\ProductOffering\Repository;

use App\ProductOffering\Entity\ProductOffering;
use App\ProductOffering\ProductOfferingMapper;

class ProductOfferingRepository
{
    protected ProductOfferingMapper $mapper;

    public function __construct(ProductOfferingMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @return ProductOffering[]
     */
    public function getProductOfferings(array $filters = []): array
    {
        // TODO Add your internal logic here for gathering product offerings
        $productOfferingsRaw = [];

        $productOfferingsMapped = [];
        foreach ($productOfferingsRaw as $productOfferingRaw) {
            $productOfferingsMapped[] = $this->mapper->from($productOfferingRaw);
        }

        return $productOfferingsMapped;
    }

    public function getProductOffering(string $id): ?ProductOffering
    {
        // TODO Add your internal logic here for gathering product offering data
        $productOfferingRaw = [];

        return $this->mapper->from($productOfferingRaw);
    }
}
