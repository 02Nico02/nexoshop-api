<?php

namespace App\Controller;

use App\Service\ApiResponder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class HealthController extends AbstractController
{
    #[Route('/api/health', methods: ['GET'])]
    public function index(ApiResponder $apiResponder): JsonResponse
    {
        return $apiResponder->detail([
            'status' => 'ok',
            'service' => 'NexoShop API',
            'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ]);
    }
}
