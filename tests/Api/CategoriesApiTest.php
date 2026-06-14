<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoriesApiTest extends WebTestCase
{
    public function testCategoriesTreeReturnsWrappedData(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/categories/tree');

        $this->assertResponseIsSuccessful();

        $payload = json_decode($client->getResponse()->getContent() ?: '{}', true);

        $this->assertArrayHasKey('data', $payload);
        $this->assertArrayHasKey('meta', $payload);
    }
}
