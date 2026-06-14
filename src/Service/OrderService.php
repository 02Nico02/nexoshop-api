<?php

namespace App\Service;

use App\Dto\Order\CreateOrderRequest;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Exception\ApiException;
use App\Repository\PaymentMethodRepository;
use App\Repository\ProductRepository;
use App\Repository\ShippingMethodRepository;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductRepository $productRepository,
        private readonly ShippingMethodRepository $shippingMethodRepository,
        private readonly PaymentMethodRepository $paymentMethodRepository,
        private readonly PricingService $pricingService,
    ) {
    }

    public function create(CreateOrderRequest $request): Order
    {
        $shippingMethod = $this->shippingMethodRepository->findOneBy(['code' => $request->shippingMethod, 'enabled' => true]);
        if (!$shippingMethod) {
            throw ApiException::badRequest('SHIPPING_METHOD_INVALID', 'Metodo de envio invalido');
        }

        $paymentMethod = $this->paymentMethodRepository->findOneBy(['code' => $request->paymentMethod, 'enabled' => true]);
        if (!$paymentMethod) {
            throw ApiException::badRequest('PAYMENT_METHOD_INVALID', 'Metodo de pago invalido');
        }

        if ($paymentMethod->isRequiresCardData()) {
            $paymentDetails = $request->paymentDetails ?? [];
            $cardHolder = trim((string) ($paymentDetails['cardHolder'] ?? ''));
            $lastFourDigits = trim((string) ($paymentDetails['lastFourDigits'] ?? ''));

            if ($cardHolder === '' || $lastFourDigits === '') {
                throw ApiException::badRequest('PAYMENT_DETAILS_REQUIRED', 'Los datos de tarjeta son requeridos para este metodo de pago');
            }
        }

        $order = new Order();
        $order->setCustomerName($request->customer->name);
        $order->setCustomerEmail($request->customer->email);
        $order->setCustomerPhone($request->customer->phone);
        $order->setShippingStreet($request->shippingAddress->street);
        $order->setShippingCity($request->shippingAddress->city);
        $order->setShippingProvince($request->shippingAddress->province);
        $order->setShippingPostcode($request->shippingAddress->postcode);
        $order->setShippingReference($request->shippingAddress->reference);
        $order->setShippingMethodCode($shippingMethod->getCode());
        $order->setShippingMethodLabel($shippingMethod->getLabel());
        $order->setShippingCost($shippingMethod->getCost());
        $order->setPaymentMethodCode($paymentMethod->getCode());
        $order->setPaymentMethodLabel($paymentMethod->getLabel());
        $order->setStatus('created');

        $subtotal = 0;
        $discountTotal = 0;
        $taxTotal = 0;

        foreach ($request->items as $itemData) {
            $product = $this->productRepository->find($itemData->productId);
            if (!$product || !$product->isEnabled()) {
                throw ApiException::notFound('PRODUCT_NOT_FOUND', 'Producto no encontrado o deshabilitado', [
                    'productId' => $itemData->productId,
                ]);
            }

            $variant = null;
            if ($product->getVariants()->count() > 0) {
                if ($itemData->variantId === null) {
                    throw ApiException::badRequest('VARIANT_REQUIRED', 'La variante es requerida para este producto', [
                        'productId' => $itemData->productId,
                    ]);
                }

                foreach ($product->getVariants() as $candidate) {
                    if ($candidate->getId() === $itemData->variantId) {
                        $variant = $candidate;
                        break;
                    }
                }

                if (!$variant || !$variant->isEnabled()) {
                    throw ApiException::notFound('VARIANT_NOT_FOUND', 'Variante invalida o deshabilitada', [
                        'productId' => $itemData->productId,
                        'variantId' => $itemData->variantId,
                    ]);
                }
            } elseif ($itemData->variantId !== null) {
                throw ApiException::badRequest('VARIANT_NOT_ALLOWED', 'El producto no admite variantes', [
                    'productId' => $itemData->productId,
                    'variantId' => $itemData->variantId,
                ]);
            }

            $availableStock = $variant ? $variant->getStock() : $product->getStock();
            if ($itemData->quantity > $availableStock) {
                throw ApiException::badRequest('INSUFFICIENT_STOCK', 'Stock insuficiente', [
                    'productId' => $itemData->productId,
                    'variantId' => $itemData->variantId,
                    'availableStock' => $availableStock,
                ]);
            }

            $unitPrice = $this->pricingService->resolveUnitPrice($product, $variant);
            $originalUnitPrice = $this->pricingService->resolveOriginalUnitPrice($product, $variant);
            $itemDiscountTotal = $this->pricingService->resolveDiscountTotal($product, $itemData->quantity, $variant);
            $itemTaxTotal = $this->pricingService->resolveTaxTotal($product, $unitPrice, $itemData->quantity);
            $itemSubtotal = $unitPrice * $itemData->quantity;

            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setVariant($variant);
            $orderItem->setProductName($product->getName());
            $orderItem->setVariantDescription($this->pricingService->buildVariantDescription($variant));
            $orderItem->setUnitPrice($unitPrice);
            $orderItem->setOriginalUnitPrice($originalUnitPrice);
            $orderItem->setDiscountPercentage($product->getDiscountPercentage());
            $orderItem->setTaxPercentage($product->getTaxPercentage());
            $orderItem->setQuantity($itemData->quantity);
            $orderItem->setDiscountTotal($itemDiscountTotal);
            $orderItem->setTaxTotal($itemTaxTotal);
            $orderItem->setSubtotal($itemSubtotal);
            $order->addItem($orderItem);

            $subtotal += $itemSubtotal;
            $discountTotal += $itemDiscountTotal;
            $taxTotal += $itemTaxTotal;

            if ($variant) {
                $variant->setStock($variant->getStock() - $itemData->quantity);
            }

            $product->setStock($product->getStock() - $itemData->quantity);
        }

        $total = $subtotal + $taxTotal + $shippingMethod->getCost();

        $order->setSubtotal($subtotal);
        $order->setDiscountTotal($discountTotal);
        $order->setTaxTotal($taxTotal);
        $order->setTotal($total);

        $this->entityManager->beginTransaction();

        try {
            $this->entityManager->persist($order);
            $this->entityManager->flush();

            $order->setOrderNumber(sprintf('NX-%06d', $order->getId()));
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $throwable) {
            $this->entityManager->rollback();
            throw $throwable;
        }

        return $order;
    }
}
