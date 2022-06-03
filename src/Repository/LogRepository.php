<?php

namespace App\Repository;

use App\Entity\Log;
use App\Dto\LogSearch;
use DateTime;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Log|null find($id, $lockMode = null, $lockVersion = null)
 * @method Log|null findOneBy(array $criteria, array $orderBy = null)
 * @method Log[]    findAll()
 * @method Log[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Log::class);
    }

    public function findAllFiltered(?LogSearch $logSearch): Query
    {
        $qb = $this->createQueryBuilder('log');
        if (null !== $logSearch) {
            if ($logSearch->getKeyword()) {
                $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('log.sented', ':search'),
                        $qb->expr()->like('log.source', ':search'),
                        $qb->expr()->like('log.destination', ':search'),
                        $qb->expr()->like('log.user', ':search'),
                        $qb->expr()->like('log.result', ':search')
                    )
                )
                    ->setParameter('search', "%{$logSearch->getKeyword()}%");
            }
        }
        $qb->orderBy('log.sented', 'desc');
        return $qb->getQuery();
    }

    public function findAllOneYearOld()
    {
        $now = new \DateTime();
        $nowLastYear = $now->modify('-1 year')->format('Y-m-d');

        return $this->createQueryBuilder('log')
            ->andWhere('log.sented < :val')
            ->setParameter('val', $nowLastYear)
            ->orderBy('log.sented', 'desc')
            ->getQuery()
            ->getResult();
    }

    public function findLastImported()
    {
        return $this->createQueryBuilder('log')
            ->orderBy('log.sented', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();   
    }
}
