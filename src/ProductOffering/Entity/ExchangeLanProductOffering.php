<?php

namespace App\ProductOffering\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;

class ExchangeLanProductOffering extends ProductOffering
{
    /**
     * @ApiProperty(
     *     description="The id of the exchange lan network service"
     * )
     */
    protected ?string $exchangeLanNetworkService = null;

    public function __construct()
    {
        $this->type = 'exchange_lan';
    }

    public function getExchangeLanNetworkService(): ?string
    {
        return $this->exchangeLanNetworkService;
    }

    public function setExchangeLanNetworkService(?string $exchangeLanNetworkService): self
    {
        $this->exchangeLanNetworkService = $exchangeLanNetworkService;
        return $this;
    }
}
