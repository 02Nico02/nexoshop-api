<?php

namespace App\Controller;

use App\Dto\Order\CheckoutItemData;
use App\Dto\Order\CreateOrderRequest;
use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderController extends AbstractController
{
    #[Route('/api/orders', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): JsonResponse
    {
        $orders = array_map(fn (Order $order) => $this->normalizeOrderSummary($order), $orderRepository->findRecent());

        return $this->json(['data' => $orders, 'count' => count($orders)]);
    }

    #[Route('/api/orders/{id<\\d+>}', methods: ['GET'])]
    public function show(int $id, OrderRepository $orderRepository): JsonResponse
    {
        $order = $orderRepository->find($id);

        if (!$order) {
            return $this->json(['error' => 'Pedido no encontrado'], 404);
        }

        return $this->json($this->normalizeOrderDetail($order));
    }

    #[Route('/api/orders', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator, OrderService $orderService): JsonResponse
    {
        try {
            $payload = $request->toArray();
        } catch (\Throwable) {
            return $this->json(['error' => 'JSON invalido'], 400);
        }

        $dto = $this->mapRequestToDto($payload);
        $violations = $validator->validate($dto);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    'field' => trim((string) $violation->getPropertyPath(), '[]'),
                    'message' => $violation->getMessage(),
                ];
            }

            return $this->json(['errors' => $errors], 422);
        }

        try {
            $order = $orderService->create($dto);
        } catch (\Symfony\Component\HttpKernel\Exception\BadRequestHttpException $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        } catch (\Throwable) {
            return $this->json(['error' => 'No se pudo crear el pedido'], 500);
        }

        return $this->json($this->normalizeOrderDetail($order), 201);
    }

    private function mapRequestToDto(array $payload): CreateOrderRequest
    {
        $dto = new CreateOrderRequest();

        $dto->customer->name = (string) ($payload['customer']['name'] ?? '');
        $dto->customer->email = (string) ($payload['customer']['email'] ?? '');
        $dto->customer->phone = (string) ($payload['customer']['phone'] ?? '');

        $dto->shippingAddress->street = (string) ($payload['shippingAddress']['street'] ?? '');
        $dto->shippingAddress->city = (string) ($payload['shippingAddress']['city'] ?? '');
        $dto->shippingAddress->province = (string) ($payload['shippingAddress']['province'] ?? '');
        $dto->shippingAddress->postcode = (string) ($payload['shippingAddress']['postcode'] ?? '');
        $dto->shippingAddress->reference = isset($payload['shippingAddress']['reference']) ? (string) $payload['shippingAddress']['reference'] : null;

        $dto->shippingMethod = (string) ($payload['shippingMethod'] ?? '');
        $dto->paymentMethod = (string) ($payload['paymentMethod'] ?? '');
        $dto->paymentDetails = isset($payload['paymentDetails']) && is_array($payload['paymentDetails']) ? $payload['paymentDetails'] : null;

        $dto->items = [];
        foreach (($payload['items'] ?? []) as $itemPayload) {
            if (!is_array($itemPayload)) {
                continue;
            }

            $item = new CheckoutItemData();
            $item->productId = (int) ($itemPayload['productId'] ?? 0);
            $item->variantId = isset($itemPayload['variantId']) ? (int) $itemPayload['variantId'] : null;
            $item->quantity = (int) ($itemPayload['quantity'] ?? 0);
            $dto->items[] = $item;
        }

        return $dto;
    }

    private function normalizeOrderSummary(Order $order): array
    {
        return [
            'id' => $order->getId(),
            'orderNumber' => $order->getOrderNumber(),
            'status' => $order->getStatus(),
            'customerName' => $order->getCustomerName(),
            'customerEmail' => $order->getCustomerEmail(),
            'subtotal' => $order->getSubtotal(),
            'total' => $order->getTotal(),
            'createdAt' => $order->getCreatedAt()?->format(DATE_ATOM),
        ];
    }

    private function normalizeOrderDetail(Order $order): array
    {
        return [
            'id' => $order->getId(),
            'orderNumber' => $order->getOrderNumber(),
            'status' => $order->getStatus(),
            'customer' => [
                'name' => $order->getCustomerName(),
                'email' => $order->getCustomerEmail(),
                'phone' => $order->getCustomerPhone(),
            ],
            'shippingAddress' => [
                'street' => $order->getShippingStreet(),
                'city' => $order->getShippingCity(),
                'province' => $order->getShippingProvince(),
                'postcode' => $order->getShippingPostcode(),
                'reference' => $order->getShippingReference(),
            ],
            'shippingMethod' => [
                'code' => $order->getShippingMethodCode(),
                'label' => $order->getShippingMethodLabel(),
                'cost' => $order->getShippingCost(),
            ],
            'paymentMethod' => [
                'code' => $order->getPaymentMethodCode(),
                'label' => $order->getPaymentMethodLabel(),
            ],
            'items' => array_map(static function ($item): array {
                return [
                    'id' => $item->getId(),
                    'productName' => $item->getProductName(),
                    'variantDescription' => $item->getVariantDescription(),
                    'quantity' => $item->getQuantity(),
                    'unitPrice' => $item->getUnitPrice(),
                    'originalUnitPrice' => $item->getOriginalUnitPrice(),
                    'discountPercentage' => $item->getDiscountPercentage(),
                    'taxPercentage' => $item->getTaxPercentage(),
                    'discountTotal' => $item->getDiscountTotal(),
                    'taxTotal' => $item->getTaxTotal(),
                    'subtotal' => $item->getSubtotal(),
                ];
            }, $order->getItems()->toArray()),
            'subtotal' => $order->getSubtotal(),
            'discountTotal' => $order->getDiscountTotal(),
            'taxTotal' => $order->getTaxTotal(),
            'shippingCost' => $order->getShippingCost(),
            'total' => $order->getTotal(),
            'createdAt' => $order->getCreatedAt()?->format(DATE_ATOM),
            'updatedAt' => $order->getUpdatedAt()?->format(DATE_ATOM),
        ];
    }
}
