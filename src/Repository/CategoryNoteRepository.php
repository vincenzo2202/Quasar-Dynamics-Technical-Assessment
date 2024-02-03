<?php

namespace App\Repository;

use App\Entity\CategoryNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CategoryNote>
 *
 * @method CategoryNote|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategoryNote|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategoryNote[]    findAll()
 * @method CategoryNote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryNoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryNote::class);
    }

//    /**
//     * @return CategoryNote[] Returns an array of CategoryNote objects
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

//    public function findOneBySomeField($value): ?CategoryNote
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
