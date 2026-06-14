<?php

namespace App\Controller;

use App\Entity\ShippingMethod;
use App\Repository\ShippingMethodRepository;
use App\Service\ApiResponder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ShippingMethodController extends AbstractController
{
    #[Route('/api/shipping-methods', methods: ['GET'])]
    public function index(ShippingMethodRepository $shippingMethodRepository, ApiResponder $apiResponder): JsonResponse
    {
        $methods = array_map(
            fn (ShippingMethod $method) => [
                'id' => $method->getId(),
                'code' => $method->getCode(),
                'label' => $method->getLabel(),
                'description' => $method->getDescription(),
                'cost' => $method->getCost(),
                'eta' => $method->getEta(),
                'enabled' => $method->isEnabled(),
                'sortOrder' => $method->getSortOrder(),
            ],
            $shippingMethodRepository->findActiveOrdered()
        );

        return $apiResponder->list($methods, [
            'total' => count($methods),
        ]);
    }
}
