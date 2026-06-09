<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @return Category[]
     */
    public function findOrdered(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.parent', 'parent')
            ->addSelect('parent')
            ->orderBy('c.level', 'ASC')
            ->addOrderBy('c.sortOrder', 'ASC')
            ->addOrderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findTree(): array
    {
        return $this->buildTree($this->findOrdered());
    }

    public function findOneBySlug(string $slug): ?Category
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * @return int[]
     */
    public function resolveContextCategoryIds(?string $categoryIdentifier, ?string $subcategoryIdentifier): array
    {
        if ($subcategoryIdentifier) {
            $subcategory = $this->findByIdentifier($subcategoryIdentifier);

            if (!$subcategory) {
                return [];
            }

            if ($categoryIdentifier) {
                $category = $this->findByIdentifier($categoryIdentifier);
                if (!$category || !$this->isDescendantOf($subcategory, $category)) {
                    return [];
                }
            }

            return $this->collectDescendantIds($subcategory);
        }

        if ($categoryIdentifier) {
            $category = $this->findByIdentifier($categoryIdentifier);

            if (!$category) {
                return [];
            }

            return $this->collectDescendantIds($category);
        }

        return [];
    }

    public function findByIdentifier(string $identifier): ?Category
    {
        if (is_numeric($identifier)) {
            return $this->find((int) $identifier);
        }

        return $this->findOneBySlug($identifier);
    }

    /**
     * @return array<int, array{id:int|null,name:string,slug:string,level:int}>
     */
    public function searchByTerm(string $term, int $limit = 5): array
    {
        $term = mb_strtolower(trim($term));

        return $this->createQueryBuilder('c')
            ->andWhere('LOWER(c.name) LIKE :term OR LOWER(c.slug) LIKE :term OR LOWER(COALESCE(c.description, \'\')) LIKE :term')
            ->setParameter('term', '%'.$term.'%')
            ->orderBy('c.level', 'ASC')
            ->addOrderBy('c.sortOrder', 'ASC')
            ->addOrderBy('c.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param Category[] $categories
     * @return array<int, array<string, mixed>>
     */
    private function buildTree(array $categories, ?int $parentId = null): array
    {
        $tree = [];

        foreach ($categories as $category) {
            $currentParentId = $category->getParent()?->getId();
            if ($currentParentId !== $parentId) {
                continue;
            }

            $tree[] = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'slug' => $category->getSlug(),
                'description' => $category->getDescription(),
                'level' => $category->getLevel(),
                'enabled' => $category->isEnabled(),
                'sortOrder' => $category->getSortOrder(),
                'children' => $this->buildTree($categories, $category->getId()),
            ];
        }

        return $tree;
    }

    /**
     * @return int[]
     */
    private function collectDescendantIds(Category $category): array
    {
        $ids = [$category->getId()];

        foreach ($category->getChildren() as $child) {
            $ids = array_merge($ids, $this->collectDescendantIds($child));
        }

        return array_values(array_unique(array_filter($ids)));
    }

    private function isDescendantOf(Category $child, Category $parent): bool
    {
        $current = $child->getParent();

        while ($current) {
            if ($current->getId() === $parent->getId()) {
                return true;
            }

            $current = $current->getParent();
        }

        return false;
    }
}
