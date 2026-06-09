<?php

namespace App\Repository;

use App\Entity\ShippingMethod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ShippingMethodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShippingMethod::class);
    }

    /**
     * @return ShippingMethod[]
     */
    public function findActiveOrdered(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.enabled = true')
            ->orderBy('s.sortOrder', 'ASC')
            ->addOrderBy('s.label', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
