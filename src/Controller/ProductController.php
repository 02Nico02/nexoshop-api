<?php

namespace App\Controller;

use App\Entity\ProductAttribute;
use App\Entity\ProductFeature;
use App\Entity\ProductImage;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\ApiResponder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/api/products', methods: ['GET'])]
    public function index(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository, ApiResponder $apiResponder): JsonResponse
    {
        $categoryIdentifier = $request->query->has('category') ? $request->query->getString('category') : null;
        $subcategoryIdentifier = $request->query->has('subcategory') ? $request->query->getString('subcategory') : null;
        $categoryIds = $categoryRepository->resolveContextCategoryIds($categoryIdentifier, $subcategoryIdentifier);
        $attributeFilters = $request->query->all('attribute');
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(max(1, $request->query->getInt('limit', 12)), 50);

        if (($categoryIdentifier !== null || $subcategoryIdentifier !== null) && $categoryIds === []) {
            return $apiResponder->list([], [
                'page' => $page,
                'limit' => $limit,
                'totalItems' => 0,
                'totalPages' => 0,
            ]);
        }

        $filters = [
            'search' => $request->query->has('search') ? $request->query->getString('search') : null,
            'categoryIds' => $categoryIds,
            'minPrice' => $request->query->has('minPrice') ? $request->query->getInt('minPrice') : null,
            'maxPrice' => $request->query->has('maxPrice') ? $request->query->getInt('maxPrice') : null,
            'featured' => $request->query->has('featured') ? filter_var($request->query->get('featured'), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) : null,
            'sort' => $request->query->has('sort') ? $request->query->getString('sort') : null,
            'attributes' => is_array($attributeFilters) ? $attributeFilters : [],
        ];

        $paginated = $productRepository->paginateByFilters($filters, $page, $limit);
        $products = array_map(
            fn (Product $product) => $this->normalizeProductSummary($product),
            $paginated['data']
        );

        return $apiResponder->list($products, [
            'page' => $paginated['page'],
            'limit' => $paginated['limit'],
            'totalItems' => $paginated['totalItems'],
            'totalPages' => $paginated['totalPages'],
        ]);
    }

    #[Route('/api/products/{id<\\d+>}', methods: ['GET'])]
    public function show(int $id, ProductRepository $productRepository, ApiResponder $apiResponder): JsonResponse
    {
        $product = $productRepository->find($id);

        if (!$product) {
            return $apiResponder->error('PRODUCT_NOT_FOUND', 'Producto no encontrado', 404);
        }

        return $apiResponder->detail($this->normalizeProductDetail($product));
    }

    #[Route('/api/products/slug/{slug}', methods: ['GET'])]
    public function showBySlug(string $slug, ProductRepository $productRepository, ApiResponder $apiResponder): JsonResponse
    {
        $product = $productRepository->findOneBySlug($slug);

        if (!$product) {
            return $apiResponder->error('PRODUCT_NOT_FOUND', 'Producto no encontrado', 404);
        }

        return $apiResponder->detail($this->normalizeProductDetail($product));
    }

    private function normalizeProductSummary(Product $product): array
    {
        return [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'slug' => $product->getSlug(),
            'description' => $product->getDescription(),
            'shortDescription' => $product->getShortDescription(),
            'category' => $product->getCategory() ? [
                'id' => $product->getCategory()->getId(),
                'name' => $product->getCategory()->getName(),
                'slug' => $product->getCategory()->getSlug(),
            ] : null,
            'basePrice' => $product->getBasePrice(),
            'originalPrice' => $product->getOriginalPrice(),
            'discountPercentage' => $product->getDiscountPercentage(),
            'discountAmount' => $product->getDiscountAmount(),
            'taxPercentage' => $product->getTaxPercentage(),
            'taxLabel' => $product->getTaxLabel(),
            'taxIncluded' => $product->isTaxIncluded(),
            'image' => $product->getImage(),
            'enabled' => $product->isEnabled(),
            'featured' => $product->isFeatured(),
            'stock' => $product->getStock(),
            'createdAt' => $product->getCreatedAt()?->format(DATE_ATOM),
            'updatedAt' => $product->getUpdatedAt()?->format(DATE_ATOM),
        ];
    }

    private function normalizeProductDetail(Product $product): array
    {
        return array_merge($this->normalizeProductSummary($product), [
            'images' => $this->normalizeImages($product),
            'features' => $this->normalizeFeatures($product),
            'attributes' => $this->normalizeAttributes($product),
            'variants' => $this->normalizeVariants($product),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeImages(Product $product): array
    {
        return array_map(
            fn (ProductImage $image) => [
                'id' => $image->getId(),
                'url' => $image->getUrl(),
                'alt' => $image->getAlt(),
                'position' => $image->getPosition(),
                'main' => $image->isMain(),
                'variantId' => $image->getVariant()?->getId(),
                'variantSku' => $image->getVariant()?->getSku(),
            ],
            $product->getImages()->toArray()
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeFeatures(Product $product): array
    {
        return array_map(
            fn (ProductFeature $feature) => [
                'id' => $feature->getId(),
                'name' => $feature->getName(),
                'value' => $feature->getValue(),
                'position' => $feature->getPosition(),
            ],
            $product->getFeatures()->toArray()
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeAttributes(Product $product): array
    {
        return array_map(
            fn (ProductAttribute $attribute) => [
                'id' => $attribute->getId(),
                'name' => $attribute->getName(),
                'value' => $attribute->getValue(),
                'filterable' => $attribute->isFilterable(),
                'facetGroup' => $attribute->getFacetGroup(),
                'sortOrder' => $attribute->getSortOrder(),
            ],
            $product->getAttributes()->toArray()
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeVariants(Product $product): array
    {
        return array_map(
            fn (ProductVariant $variant) => [
                'id' => $variant->getId(),
                'sku' => $variant->getSku(),
                'options' => $variant->getOptions(),
                'stock' => $variant->getStock(),
                'priceDelta' => $variant->getPriceDelta(),
                'image' => $variant->getImage(),
                'enabled' => $variant->isEnabled(),
                'label' => $this->buildVariantLabel($variant),
            ],
            $product->getVariants()->toArray()
        );
    }

    private function buildVariantLabel(ProductVariant $variant): string
    {
        $parts = [];
        foreach ($variant->getOptions() as $key => $value) {
            $parts[] = ucfirst((string) $key).': '.$value;
        }

        return implode(' / ', $parts);
    }
}
