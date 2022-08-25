<?php

use App\Security\Role\RoleFactory;
use App\Tests\Helper\ApiTestCase;

class ProductOfferingsTest extends ApiTestCase
{
    public function testGetProductOfferings()
    {
        $jwt = $this->getValidJwtForUserWithPermission(
            ApiTestCase::VALID_API_COMPANY,
            RoleFactory::GET_PRODUCT_OFFERINGS
        );
        $response = $this->request(
            'GET',
            '/api/v2/product-offerings',
            null,
            ['Authorization' => 'Bearer ' . $jwt]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = \json_decode($response->getContent(), true);
        $this->assertTrue(is_array($responseData));
        $this->assertNotEmpty($responseData);

        foreach ($responseData as $product) {
            $this->validateProductOfferingEntity($product);
        }
    }

    /**
     * @param $product
     */
    protected function validateProductOfferingEntity($product): void
    {
        $keys = [
            "type",
            "id",
            "name",
            "display_name",
            "provider_vlans",
            "resource_type",
            "handover_metro_area",
            "handover_metro_area_network",
            "service_metro_area",
            "service_metro_area_network",
            "bandwidth_min",
            "bandwidth_max",
            "physical_port_speed",
            "service_provider",
            "downgrade_allowed",
            "upgrade_allowed",
        ];

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $product);
        }

        if ($product['type'] === 'exchange_lan') {
            $this->assertArrayHasKey('exchange_lan_network_service', $product);
        }
    }
}
