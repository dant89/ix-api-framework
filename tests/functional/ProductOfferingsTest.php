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

        # TODO add further test coverage to suite your requirements
    }
}
