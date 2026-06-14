<?php

namespace App\Controller;

use App\Dto\Order\CheckoutItemData;
use App\Dto\Order\CreateOrderRequest;
use App\Entity\Order;
use App\Exception\ApiException;
use App\Repository\OrderRepository;
use App\Service\OrderService;
use App\Service\ApiResponder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderController extends AbstractController
{
    #[Route('/api/orders', methods: ['GET'])]
    public function index(OrderRepository $orderRepository, ApiResponder $apiResponder): JsonResponse
    {
        $orders = array_map(fn (Order $order) => $this->normalizeOrderSummary($order), $orderRepository->findRecent());

        return $apiResponder->list($orders, [
            'total' => count($orders),
        ]);
    }

    #[Route('/api/orders/{id<\\d+>}', methods: ['GET'])]
    public function show(int $id, OrderRepository $orderRepository, ApiResponder $apiResponder): JsonResponse
    {
        $order = $orderRepository->find($id);

        if (!$order) {
            return $apiResponder->error('ORDER_NOT_FOUND', 'Pedido no encontrado', 404);
        }

        return $apiResponder->detail($this->normalizeOrderDetail($order));
    }

    #[Route('/api/orders', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator, OrderService $orderService, ApiResponder $apiResponder): JsonResponse
    {
        try {
            $payload = $request->toArray();
        } catch (\Throwable) {
            return $apiResponder->error('INVALID_JSON', 'JSON invalido', 400);
        }

        $dto = $this->mapRequestToDto($payload);
        $violations = $validator->validate($dto);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    'field' => $this->normalizeViolationPath((string) $violation->getPropertyPath()),
                    'message' => $violation->getMessage(),
                ];
            }

            return $apiResponder->validationErrors($errors);
        }

        try {
            $order = $orderService->create($dto);
        } catch (ApiException $exception) {
            return $apiResponder->error(
                $exception->getErrorCode(),
                $exception->getMessage(),
                $exception->getStatusCode(),
                $exception->getDetails()
            );
        } catch (\Throwable) {
            return $apiResponder->error('ORDER_CREATE_FAILED', 'No se pudo crear el pedido', 500);
        }

        return $apiResponder->detail($this->normalizeOrderDetail($order), 201);
    }

    private function mapRequestToDto(array $payload): CreateOrderRequest
    {
        $dto = new CreateOrderRequest();

        $dto->customer->name = $this->payloadString($payload, ['customer', 'name']);
        $dto->customer->email = $this->payloadString($payload, ['customer', 'email']);
        $dto->customer->phone = $this->payloadString($payload, ['customer', 'phone']);

        $dto->shippingAddress->street = $this->payloadString($payload, ['shippingAddress', 'street']);
        $dto->shippingAddress->city = $this->payloadString($payload, ['shippingAddress', 'city']);
        $dto->shippingAddress->province = $this->payloadString($payload, ['shippingAddress', 'province']);
        $dto->shippingAddress->postcode = $this->payloadString($payload, ['shippingAddress', 'postcode']);
        $dto->shippingAddress->reference = $this->payloadNullableString($payload, ['shippingAddress', 'reference']);

        $dto->shippingMethod = $this->payloadString($payload, ['shippingMethod']);
        $dto->paymentMethod = $this->payloadString($payload, ['paymentMethod']);
        $paymentDetails = $this->payloadArray($payload, ['paymentDetails']);
        $dto->paymentDetails = $paymentDetails !== [] ? $paymentDetails : null;

        $dto->items = [];
        foreach ($this->payloadArray($payload, ['items']) as $itemPayload) {
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

    private function payloadString(array $payload, array $path): string
    {
        $value = $this->payloadValue($payload, $path);
        return is_scalar($value) ? trim((string) $value) : '';
    }

    private function payloadNullableString(array $payload, array $path): ?string
    {
        $value = $this->payloadValue($payload, $path);
        if ($value === null || $value === '') {
            return null;
        }

        return is_scalar($value) ? trim((string) $value) : null;
    }

    /**
     * @return array<int|string, mixed>
     */
    private function payloadArray(array $payload, array $path): array
    {
        $value = $this->payloadValue($payload, $path);
        return is_array($value) ? $value : [];
    }

    private function payloadValue(array $payload, array $path): mixed
    {
        $current = $payload;
        foreach ($path as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return null;
            }

            $current = $current[$segment];
        }

        return $current;
    }

    private function normalizeViolationPath(string $propertyPath): string
    {
        $normalized = str_replace(['[', ']'], ['.', ''], $propertyPath);
        $normalized = preg_replace('/\.+/', '.', $normalized) ?? $normalized;

        return trim($normalized, '.');
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
