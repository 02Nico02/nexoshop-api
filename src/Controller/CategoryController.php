<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Service\ApiResponder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class CategoryController extends AbstractController
{
    #[Route('/api/categories/tree', methods: ['GET'])]
    public function tree(CategoryRepository $categoryRepository, ApiResponder $apiResponder): JsonResponse
    {
        $tree = $categoryRepository->findTree();

        return $apiResponder->list($tree, [
            'total' => count($tree),
        ]);
    }

    #[Route('/api/categories', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository, ApiResponder $apiResponder): JsonResponse
    {
        $categories = array_map(
            fn (Category $category) => $this->normalizeCategory($category),
            $categoryRepository->findOrdered()
        );

        return $apiResponder->list($categories, [
            'total' => count($categories),
        ]);
    }

    #[Route('/api/categories/{id<\\d+>}', methods: ['GET'])]
    public function show(int $id, CategoryRepository $categoryRepository, ApiResponder $apiResponder): JsonResponse
    {
        $category = $categoryRepository->find($id);

        if (!$category) {
            return $apiResponder->error('CATEGORY_NOT_FOUND', 'Categoria no encontrada', 404);
        }

        return $apiResponder->detail($this->normalizeCategory($category));
    }

    #[Route('/api/categories/slug/{slug}', methods: ['GET'])]
    public function showBySlug(string $slug, CategoryRepository $categoryRepository, ApiResponder $apiResponder): JsonResponse
    {
        $category = $categoryRepository->findOneBySlug($slug);

        if (!$category) {
            return $apiResponder->error('CATEGORY_NOT_FOUND', 'Categoria no encontrada', 404);
        }

        return $apiResponder->detail($this->normalizeCategory($category));
    }

    private function normalizeCategory(Category $category): array
    {
        return [
            'id' => $category->getId(),
            'name' => $category->getName(),
            'slug' => $category->getSlug(),
            'description' => $category->getDescription(),
            'level' => $category->getLevel(),
            'enabled' => $category->isEnabled(),
            'sortOrder' => $category->getSortOrder(),
            'parent' => $category->getParent() ? [
                'id' => $category->getParent()->getId(),
                'name' => $category->getParent()->getName(),
                'slug' => $category->getParent()->getSlug(),
            ] : null,
            'breadcrumbs' => $this->buildBreadcrumbs($category),
            'createdAt' => $category->getCreatedAt()?->format(DATE_ATOM),
            'updatedAt' => $category->getUpdatedAt()?->format(DATE_ATOM),
        ];
    }

    /**
     * @return array<int, array{id:int|null,name:string,slug:string}>
     */
    private function buildBreadcrumbs(Category $category): array
    {
        $trail = [];
        $current = $category;

        while ($current) {
            $trail[] = [
                'id' => $current->getId(),
                'name' => $current->getName(),
                'slug' => $current->getSlug(),
            ];

            $current = $current->getParent();
        }

        return array_reverse($trail);
    }
}
