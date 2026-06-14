<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CorsApiTest extends WebTestCase
{
    public function testPreflightRequestReturnsCorsHeaders(): void
    {
        $client = static::createClient();
        $client->request('OPTIONS', '/api/products', server: [
            'HTTP_ORIGIN' => 'http://localhost:4200',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'GET',
        ]);

        $this->assertResponseStatusCodeSame(204);
        $this->assertResponseHeaderSame('access-control-allow-origin', 'http://localhost:4200');
    }
}
