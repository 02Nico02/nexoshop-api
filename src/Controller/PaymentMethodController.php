<?php

namespace App\Controller;

use App\Entity\PaymentMethod;
use App\Repository\PaymentMethodRepository;
use App\Service\ApiResponder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class PaymentMethodController extends AbstractController
{
    #[Route('/api/payment-methods', methods: ['GET'])]
    public function index(PaymentMethodRepository $paymentMethodRepository, ApiResponder $apiResponder): JsonResponse
    {
        $methods = array_map(
            fn (PaymentMethod $method) => [
                'id' => $method->getId(),
                'code' => $method->getCode(),
                'label' => $method->getLabel(),
                'description' => $method->getDescription(),
                'type' => $method->getType(),
                'requiresCardData' => $method->isRequiresCardData(),
                'enabled' => $method->isEnabled(),
                'sortOrder' => $method->getSortOrder(),
            ],
            $paymentMethodRepository->findActiveOrdered()
        );

        return $apiResponder->list($methods, [
            'total' => count($methods),
        ]);
    }
}
