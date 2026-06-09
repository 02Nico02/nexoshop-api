<?php

namespace App\Repository;

use App\Entity\PaymentMethod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PaymentMethodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentMethod::class);
    }

    /**
     * @return PaymentMethod[]
     */
    public function findActiveOrdered(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.enabled = true')
            ->orderBy('p.sortOrder', 'ASC')
            ->addOrderBy('p.label', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
