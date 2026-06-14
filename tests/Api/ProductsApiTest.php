<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductsApiTest extends WebTestCase
{
    public function testProductsListReturnsPaginatedShape(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/products?page=1&limit=12');

        $this->assertResponseIsSuccessful();

        $payload = json_decode($client->getResponse()->getContent() ?: '{}', true);

        $this->assertArrayHasKey('data', $payload);
        $this->assertArrayHasKey('meta', $payload);
        $this->assertSame(1, $payload['meta']['page'] ?? null);
        $this->assertSame(12, $payload['meta']['limit'] ?? null);
    }

    public function testProductDetailReturnsWrappedData(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/products/1');

        $this->assertTrue(in_array($client->getResponse()->getStatusCode(), [200, 404], true));

        $payload = json_decode($client->getResponse()->getContent() ?: '{}', true);
        if ($client->getResponse()->getStatusCode() === 200) {
            $this->assertArrayHasKey('data', $payload);
            $this->assertSame(1, $payload['data']['id'] ?? null);
        } else {
            $this->assertSame('PRODUCT_NOT_FOUND', $payload['error']['code'] ?? null);
        }
    }
}
