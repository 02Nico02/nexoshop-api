<?php

namespace App\Service;

use App\Dto\Order\CreateOrderRequest;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\PaymentMethodRepository;
use App\Repository\ProductRepository;
use App\Repository\ShippingMethodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
            throw new BadRequestHttpException('Metodo de envio invalido');
        }

        $paymentMethod = $this->paymentMethodRepository->findOneBy(['code' => $request->paymentMethod, 'enabled' => true]);
        if (!$paymentMethod) {
            throw new BadRequestHttpException('Metodo de pago invalido');
        }

        if ($paymentMethod->isRequiresCardData()) {
            $cardHolder = trim((string) ($request->paymentDetails['cardHolder'] ?? ''));
            $lastFourDigits = trim((string) ($request->paymentDetails['lastFourDigits'] ?? ''));

            if ($cardHolder === '' || $lastFourDigits === '') {
                throw new BadRequestHttpException('Los datos de tarjeta son requeridos para este metodo de pago');
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
                throw new BadRequestHttpException('Producto no encontrado o deshabilitado');
            }

            $variant = null;
            if ($product->getVariants()->count() > 0) {
                if ($itemData->variantId === null) {
                    throw new BadRequestHttpException('La variante es requerida para este producto');
                }

                foreach ($product->getVariants() as $candidate) {
                    if ($candidate->getId() === $itemData->variantId) {
                        $variant = $candidate;
                        break;
                    }
                }

                if (!$variant || !$variant->isEnabled()) {
                    throw new BadRequestHttpException('Variante invalida o deshabilitada');
                }
            } elseif ($itemData->variantId !== null) {
                throw new BadRequestHttpException('El producto no admite variantes');
            }

            $availableStock = $variant ? $variant->getStock() : $product->getStock();
            if ($itemData->quantity > $availableStock) {
                throw new BadRequestHttpException('Stock insuficiente');
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
