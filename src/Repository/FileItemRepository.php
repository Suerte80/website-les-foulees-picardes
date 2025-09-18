<?php

namespace App\Repository;

use App\Entity\FileItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FileItem>
 */
class FileItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileItem::class);
    }

    public function findRootsWithChildren(int $depth = 1): array
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.children', 'c1')->addSelect('c1')
            ->andWhere('i.parent IS NULL');

        if ($depth >= 2) {
            $qb->leftJoin('c1.children', 'c2')->addSelect('c2');
        }
        if ($depth >= 3) {
            $qb->leftJoin('c2.children', 'c3')->addSelect('c3');
        }

        return $qb->distinct(true)
            ->orderBy('i.type', 'ASC')->addOrderBy('i.name', 'ASC')
            ->getQuery()->getResult();
    }

    //    /**
    //     * @return FileItem[] Returns an array of FileItem objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?FileItem
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
