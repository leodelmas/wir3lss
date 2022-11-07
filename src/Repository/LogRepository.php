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
        if (null !== $logSearch && $logSearch->getKeyword()) {
            
            if ($logSearch->getKeyword() == "Succès" || $logSearch->getKeyword() == "Bloqué") {
                switch ($logSearch->getKeyword()) {
                    case "Succès":
                        $qb
                            ->andWhere('log.result = :result')
                            ->setParameter('result', 'CONNECT');
                        break;
                    case "Bloqué":
                        $qb
                            ->andWhere('log.result = :firstResult OR log.result = :secondResult')
                            ->setParameter('firstResult', 'CONNECT REDIRECT')
                            ->setParameter('secondResult', 'GET REDIRECT');
                        break;
                }
            }
            else  {
                if (preg_match("/^([0-2][0-9]|(3)[0-1])(\/)(((0)[0-9])|((1)[0-2]))(\/)\d{4}$/", $logSearch->getKeyword())) {
                    $sented = DateTime::createFromFormat("d/m/Y", $logSearch->getKeyword());
                    $search = $sented->format('Y-m-d');
                }
                else {
                    $search = $logSearch->getKeyword();
                }

                $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('log.sented', ':search'),
                        $qb->expr()->like('log.source', ':search'),
                        $qb->expr()->like('log.destination', ':search'),
                        $qb->expr()->like('log.user', ':search')
                    )
                )
                    ->setParameter('search', "%{$search}%");
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

    public function findAllForExport()
    {
        return $this->createQueryBuilder('log')
            ->getQuery()
            ->getArrayResult();
    }

    public function findLastImported()
    {
        return $this->createQueryBuilder('log')
            ->orderBy('log.sented', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();   
    }

    public function findNumberByUser()
    {
        return $this->createQueryBuilder('log')
            ->select('COUNT(log.id) as count, log.user')
            ->groupBy('log.user')
            ->getQuery()
            ->getArrayResult();   
    }

    public function findNumberByDate()
    {
        return $this->createQueryBuilder('log')
            ->select('COUNT(log.id) as count, DATE(log.sented) as date')
            ->groupBy('date')
            ->getQuery()
            ->getArrayResult();   
    }
}
