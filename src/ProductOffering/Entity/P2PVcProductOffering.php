<?php

namespace App\ProductOffering\Entity;

class P2PVcProductOffering extends ProductOffering
{
    public function __construct()
    {
        $this->type = 'p2p_vc';
    }
}
