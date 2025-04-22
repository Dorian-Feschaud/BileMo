<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customer>
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    /**
     * @return Customer[] Returns an array of Customer objects
     */
    public function findByPageLimit(int $page = 1, int $limit = 10, ?int $customerId = null): array
    {
        if ($customerId != null) {
            $queryBuiler = $this->createQueryBuilder('c')
                ->where('c.id = :customerId')
                ->setParameter('customerId', $customerId)
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);
        }
        else {
            $queryBuiler = $this->createQueryBuilder('c')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);
        }

        return $queryBuiler->getQuery()->getResult();
    }

//    /**
//     * @return Customer[] Returns an array of Customer objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Customer
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
