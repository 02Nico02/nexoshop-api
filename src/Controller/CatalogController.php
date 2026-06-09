<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductAttribute;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class CatalogController extends AbstractController
{
    #[Route('/api/catalog/facets', methods: ['GET'])]
    public function facets(Request $request, CategoryRepository $categoryRepository, ProductRepository $productRepository): JsonResponse
    {
        $categoryIdentifier = $request->query->has('category') ? $request->query->getString('category') : null;
        $subcategoryIdentifier = $request->query->has('subcategory') ? $request->query->getString('subcategory') : null;
        $categoryIds = $categoryRepository->resolveContextCategoryIds($categoryIdentifier, $subcategoryIdentifier);

        if (($categoryIdentifier !== null || $subcategoryIdentifier !== null) && $categoryIds === []) {
            return $this->json([
                'context' => [
                    'category' => $categoryIdentifier,
                    'subcategory' => $subcategoryIdentifier,
                ],
                'price' => ['min' => null, 'max' => null],
                'attributes' => [],
            ]);
        }

        $products = $productRepository->findByFilters([
            'categoryIds' => $categoryIds,
            'sort' => 'newest',
        ]);

        if ($products === []) {
            return $this->json([
                'context' => [
                    'category' => $categoryIdentifier,
                    'subcategory' => $subcategoryIdentifier,
                ],
                'price' => ['min' => null, 'max' => null],
                'attributes' => [],
            ]);
        }

        return $this->json([
            'context' => [
                'category' => $categoryIdentifier,
                'subcategory' => $subcategoryIdentifier,
            ],
            'price' => $this->buildPriceFacet($products),
            'attributes' => $this->buildAttributeFacets($products),
        ]);
    }

    /**
     * @param Product[] $products
     */
    private function buildPriceFacet(array $products): array
    {
        $prices = array_map(static fn (Product $product) => $product->getBasePrice(), $products);

        return [
            'min' => min($prices),
            'max' => max($prices),
        ];
    }

    /**
     * @param Product[] $products
     * @return array<int, array<string, mixed>>
     */
    private function buildAttributeFacets(array $products): array
    {
        $facets = [];

        foreach ($products as $product) {
            foreach ($product->getAttributes() as $attribute) {
                if (!$attribute instanceof ProductAttribute || !$attribute->isFilterable()) {
                    continue;
                }

                $key = mb_strtolower($attribute->getName());
                if (!isset($facets[$key])) {
                    $facets[$key] = [
                        'name' => $key,
                        'label' => ucfirst($attribute->getName()),
                        'facetGroup' => $attribute->getFacetGroup(),
                        'values' => [],
                    ];
                }

                $valueKey = mb_strtolower($attribute->getValue());
                if (!isset($facets[$key]['values'][$valueKey])) {
                    $facets[$key]['values'][$valueKey] = [
                        'value' => $attribute->getValue(),
                        'count' => 0,
                    ];
                }

                $facets[$key]['values'][$valueKey]['count']++;
            }
        }

        return array_values(array_map(static function (array $facet): array {
            $facet['values'] = array_values($facet['values']);
            return $facet;
        }, $facets));
    }
}
