<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HealthApiTest extends WebTestCase
{
    public function testHealthEndpointReturnsOk(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/health');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderContains('content-type', 'application/json');

        $payload = json_decode($client->getResponse()->getContent() ?: '{}', true);

        $this->assertSame('ok', $payload['data']['status'] ?? null);
        $this->assertSame('NexoShop API', $payload['data']['service'] ?? null);
    }
}
