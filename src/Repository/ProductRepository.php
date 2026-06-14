<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\ProductAttribute;
use App\Entity\ProductImage;
use App\Entity\ProductVariant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @param array{
     *     search?:string|null,
     *     categoryIds?: array<int>,
     *     minPrice?:int|null,
     *     maxPrice?:int|null,
     *     featured?:bool|null,
     *     sort?:string|null,
     *     attributes?: array<string, string>
     * } $filters
     * @return Product[]
     */
    public function findByFilters(array $filters): array
    {
        return $this->createFilteredQueryBuilder($filters)
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array{
     *     search?:string|null,
     *     categoryIds?: array<int>,
     *     minPrice?:int|null,
     *     maxPrice?:int|null,
     *     featured?:bool|null,
     *     sort?:string|null,
     *     attributes?: array<string, string>
     * } $filters
     * @return array{data: Product[], totalItems: int, totalPages: int, page: int, limit: int}
     */
    public function paginateByFilters(array $filters, int $page = 1, int $limit = 12): array
    {
        $page = max(1, $page);
        $limit = min(max(1, $limit), 50);

        $baseQb = $this->createFilteredQueryBuilder($filters);

        $countQb = clone $baseQb;
        $totalItems = (int) $countQb
            ->select('COUNT(DISTINCT p.id)')
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult();

        $data = $baseQb
            ->distinct()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return [
            'data' => $data,
            'totalItems' => $totalItems,
            'totalPages' => $totalItems > 0 ? (int) ceil($totalItems / $limit) : 0,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    /**
     * @param array{
     *     search?:string|null,
     *     categoryIds?: array<int>,
     *     minPrice?:int|null,
     *     maxPrice?:int|null,
     *     featured?:bool|null,
     *     sort?:string|null,
     *     attributes?: array<string, string>
     * } $filters
     */
    private function createFilteredQueryBuilder(array $filters)
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c')
            ->leftJoin('c.parent', 'parent')
            ->addSelect('parent')
            ->leftJoin('p.attributes', 'pa')
            ->addSelect('pa')
            ->andWhere('p.enabled = true');

        if (!empty($filters['search'])) {
            $qb->andWhere('(LOWER(p.name) LIKE :search OR LOWER(COALESCE(p.description, \'\')) LIKE :search OR LOWER(COALESCE(p.shortDescription, \'\')) LIKE :search OR LOWER(c.name) LIKE :search OR LOWER(c.slug) LIKE :search OR LOWER(COALESCE(pa.name, \'\')) LIKE :search OR LOWER(COALESCE(pa.value, \'\')) LIKE :search)')
                ->setParameter('search', '%'.mb_strtolower(trim($filters['search'])).'%');
        }

        if (!empty($filters['categoryIds'])) {
            $qb->andWhere('c.id IN (:categoryIds)')->setParameter('categoryIds', array_values($filters['categoryIds']));
        }

        if (isset($filters['minPrice']) && $filters['minPrice'] > 0) {
            $qb->andWhere('p.basePrice >= :minPrice')->setParameter('minPrice', $filters['minPrice']);
        }

        if (isset($filters['maxPrice']) && $filters['maxPrice'] > 0) {
            $qb->andWhere('p.basePrice <= :maxPrice')->setParameter('maxPrice', $filters['maxPrice']);
        }

        $featured = $filters['featured'] ?? null;
        if ($featured !== null) {
            $qb->andWhere('p.featured = :featured')->setParameter('featured', (bool) $featured);
        }

        if (!empty($filters['attributes'])) {
            $index = 0;
            foreach (($filters['attributes'] ?? []) as $name => $value) {
                $alias = 'attributeFilter'.$index;
                $qb->andWhere(
                    $qb->expr()->exists(
                        'SELECT 1 FROM '.ProductAttribute::class.' '.$alias.' WHERE '.$alias.'.product = p AND LOWER('.$alias.'.name) = :'.$alias.'Name AND LOWER('.$alias.'.value) = :'.$alias.'Value'
                    )
                )
                ->setParameter($alias.'Name', mb_strtolower(trim((string) $name)))
                ->setParameter($alias.'Value', mb_strtolower(trim((string) $value)));
                $index++;
            }
        }

        $sort = $filters['sort'] ?? 'newest';
        match ($sort) {
            'price_asc' => $qb->orderBy('p.basePrice', 'ASC'),
            'price_desc' => $qb->orderBy('p.basePrice', 'DESC'),
            'name_asc' => $qb->orderBy('p.name', 'ASC'),
            'name_desc' => $qb->orderBy('p.name', 'DESC'),
            'oldest' => $qb->orderBy('p.createdAt', 'ASC'),
            default => $qb->orderBy('p.createdAt', 'DESC'),
        };

        return $qb;
    }

    public function findOneBySlug(string $slug): ?Product
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * @return Product[]
     */
    public function searchForSuggestions(string $term, int $limit = 5): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c')
            ->andWhere('p.enabled = true')
            ->andWhere('(LOWER(p.name) LIKE :term OR LOWER(COALESCE(p.description, \'\')) LIKE :term OR LOWER(COALESCE(p.shortDescription, \'\')) LIKE :term OR LOWER(c.name) LIKE :term OR LOWER(c.slug) LIKE :term)')
            ->setParameter('term', '%'.mb_strtolower(trim($term)).'%')
            ->orderBy('p.featured', 'DESC')
            ->addOrderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, array{name:string,value:string,productId:int|null,productName:string,categoryId:int|null,categoryName:string,categorySlug:string}>
     */
    public function searchAttributeSuggestions(string $term, int $limit = 5): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('LOWER(a.name) AS name', 'a.value AS value', 'IDENTITY(a.product) AS productId', 'p.name AS productName', 'c.id AS categoryId', 'c.name AS categoryName', 'c.slug AS categorySlug')
            ->from(ProductAttribute::class, 'a')
            ->innerJoin('a.product', 'p')
            ->innerJoin('p.category', 'c')
            ->where('LOWER(a.name) LIKE :term OR LOWER(a.value) LIKE :term')
            ->setParameter('term', '%'.mb_strtolower(trim($term)).'%')
            ->orderBy('a.name', 'ASC')
            ->addOrderBy('a.value', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();
    }
}
