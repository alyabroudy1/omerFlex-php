<?php

namespace App\Repository;

use App\Entity\MovieSource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MovieSource>
 *
 * @method MovieSource|null find($id, $lockMode = null, $lockVersion = null)
 * @method MovieSource|null findOneBy(array $criteria, array $orderBy = null)
 * @method MovieSource[]    findAll()
 * @method MovieSource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovieSourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MovieSource::class);
    }

//    /**
//     * @return MovieSource[] Returns an array of MovieSource objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MovieSource
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
