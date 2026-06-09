<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    #[Route('/api/search/suggestions', methods: ['GET'])]
    public function suggestions(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository): JsonResponse
    {
        $term = trim($request->query->getString('q', ''));

        if ($term === '') {
            return $this->json([]);
        }

        $suggestions = [];

        foreach ($productRepository->searchForSuggestions($term, 5) as $product) {
            $suggestions[] = [
                'label' => $product->getName(),
                'type' => 'Producto',
                'hint' => $this->buildCategoryHint($product->getCategory()),
                'productId' => $product->getId(),
            ];
        }

        foreach ($categoryRepository->searchByTerm($term, 5) as $category) {
            $suggestions[] = [
                'label' => $category['name'],
                'type' => ((int) $category['level'] === 0) ? 'Categoria' : 'Subcategoria',
                'hint' => ((int) $category['level'] === 0) ? 'Categoria principal' : 'Subcategoria',
                'categoryId' => $category['id'],
            ];
        }

        foreach ($productRepository->searchAttributeSuggestions($term, 5) as $attribute) {
            $suggestions[] = [
                'label' => ucfirst((string) $attribute['name']),
                'type' => 'Atributo',
                'hint' => $attribute['value'].' - '.$attribute['categoryName'],
                'productId' => (int) $attribute['productId'],
            ];
        }

        $suggestions = $this->deduplicateSuggestions($suggestions);

        return $this->json(array_slice($suggestions, 0, 10));
    }

    private function buildCategoryHint(?Category $category): ?string
    {
        if (!$category) {
            return null;
        }

        $parts = [];
        $current = $category;

        while ($current) {
            $parts[] = $current->getName();
            $current = $current->getParent();
        }

        return implode(' / ', array_reverse($parts));
    }

    /**
     * @param array<int, array<string, mixed>> $suggestions
     * @return array<int, array<string, mixed>>
     */
    private function deduplicateSuggestions(array $suggestions): array
    {
        $seen = [];
        $result = [];

        foreach ($suggestions as $suggestion) {
            $key = strtolower($suggestion['type'].'|'.$suggestion['label'].'|'.($suggestion['hint'] ?? ''));
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $result[] = $suggestion;
        }

        return $result;
    }
}
