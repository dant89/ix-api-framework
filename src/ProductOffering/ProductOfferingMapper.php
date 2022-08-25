<?php

namespace App\ProductOffering;

use App\ProductOffering\Entity\ExchangeLanProductOffering;
use App\ProductOffering\Entity\Mp2MpVcProductOffering;
use App\ProductOffering\Entity\P2PVcProductOffering;
use App\ProductOffering\Entity\ProductOffering;

class ProductOfferingMapper
{
    public function supports(string $itemClass): bool
    {
        return ProductOffering::class === $itemClass;
    }

    /**
     * Used to map stored data into IX-API schema format
     */
    public function from(array $data): ProductOffering
    {
        $productOffering = null;

        if ($data['type'] === 'exchange_lan') {
            $productOffering = new ExchangeLanProductOffering();
        } elseif ($data['type'] === 'p2p_vc') {
            $productOffering = new P2PVcProductOffering();
        } elseif ($data['type'] === 'mp2mp_vc') {
            $productOffering = new Mp2MpVcProductOffering();
        }

        // TODO add your internal mapping of database to ix-api schema

        return $productOffering;
    }

    /**
     * Used to map IX-API schema format to stored format
     */
    public function to(object $item): array
    {
        // TODO add your internal mapping of ix-api schema to database

        return [];
    }
}
