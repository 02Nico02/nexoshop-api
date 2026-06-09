<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\ProductVariant;

class PricingService
{
    public function resolveUnitPrice(Product $product, ?ProductVariant $variant = null): int
    {
        return $product->getBasePrice() + ($variant?->getPriceDelta() ?? 0);
    }

    public function resolveOriginalUnitPrice(Product $product, ?ProductVariant $variant = null): int
    {
        $original = $product->getOriginalPrice() ?? $product->getBasePrice();
        return $original + ($variant?->getPriceDelta() ?? 0);
    }

    public function resolveDiscountTotal(Product $product, int $quantity, ?ProductVariant $variant = null): int
    {
        $original = $this->resolveOriginalUnitPrice($product, $variant);
        $unit = $this->resolveUnitPrice($product, $variant);

        return max(0, ($original - $unit) * $quantity);
    }

    public function resolveTaxTotal(Product $product, int $unitPrice, int $quantity): int
    {
        if ($product->isTaxIncluded()) {
            return 0;
        }

        return (int) round($unitPrice * ($product->getTaxPercentage() / 100)) * $quantity;
    }

    public function buildVariantDescription(?ProductVariant $variant = null): ?string
    {
        if (!$variant) {
            return null;
        }

        $parts = [];
        foreach ($variant->getOptions() as $key => $value) {
            $parts[] = ucfirst((string) $key).': '.$value;
        }

        return implode(' / ', $parts);
    }
}
