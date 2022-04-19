<?php

namespace App\Repository;

use App\Entity\Match\Match;
use App\Entity\Season\Season;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Match|null find($id, $lockMode = null, $lockVersion = null)
 * @method Match|null findOneBy(array $criteria, array $orderBy = null)
 * @method Match[]    findAll()
 * @method Match[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MatchRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Match::class);
    }

    /**
     * Returns earliest next match in given season
     * @param Season $season
     * @return Match|null
     */
    public function getNextInSeason(Season $season): ?Match {
        $collection = $this->createQueryBuilder("m")
            ->andWhere("m.startDate > :date")
            ->andWhere("m.status != :status")
            ->setParameter("date", new DateTime())
            ->setParameter("status", Match::FINAL_)
            ->orderBy("m.startDate", "ASC")
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if (count($collection) > 0) {
            return $collection[0];
        }
        return null;
    }
    // /**
    //  * @return Match[] Returns an array of Match objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder("m")
            ->andWhere("m.exampleField = :val")
            ->setParameter("val", $value)
            ->orderBy("m.id", "ASC")
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Match
    {
        return $this->createQueryBuilder("m")
            ->andWhere("m.exampleField = :val")
            ->setParameter("val", $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
