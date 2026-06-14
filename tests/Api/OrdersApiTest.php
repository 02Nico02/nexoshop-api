<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrdersApiTest extends WebTestCase
{
    public function testOrderValidationFailsWithoutItems(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/orders', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'customer' => [
                'name' => 'Nicolas Romero',
                'email' => 'nico@email.com',
                'phone' => '1122334455',
            ],
            'shippingAddress' => [
                'street' => 'Calle 123',
                'city' => 'Chascomus',
                'province' => 'Buenos Aires',
                'postcode' => '7130',
            ],
            'shippingMethod' => 'standard',
            'paymentMethod' => 'credit_card',
        ], JSON_THROW_ON_ERROR));

        $this->assertResponseStatusCodeSame(422);

        $payload = json_decode($client->getResponse()->getContent() ?: '{}', true);

        $this->assertArrayHasKey('errors', $payload);
    }

    public function testOrderValidationFailsWithInvalidEmail(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/orders', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'customer' => [
                'name' => 'Nicolas Romero',
                'email' => 'invalid-email',
                'phone' => '1122334455',
            ],
            'shippingAddress' => [
                'street' => 'Calle 123',
                'city' => 'Chascomus',
                'province' => 'Buenos Aires',
                'postcode' => '7130',
            ],
            'shippingMethod' => 'standard',
            'paymentMethod' => 'bank_transfer',
            'items' => [
                [
                    'productId' => 1,
                    'quantity' => 1,
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        $this->assertResponseStatusCodeSame(422);

        $payload = json_decode($client->getResponse()->getContent() ?: '{}', true);

        $this->assertArrayHasKey('errors', $payload);
    }
}
