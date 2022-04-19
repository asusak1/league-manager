<?php

namespace App\Repository;

use App\Entity\Competition\Competition;
use App\Entity\Season\Season;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Season|null find($id, $lockMode = null, $lockVersion = null)
 * @method Season|null findOneBy(array $criteria, array $orderBy = null)
 * @method Season[]    findAll()
 * @method Season[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeasonRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Season::class);
    }

    /**
     * Returns latest season for given competition
     * @param Competition $competition
     * @return Season|null
     */
    public function getLatestForCompetition(Competition $competition): ?Season {

        $collection = $this->createQueryBuilder("e")
            ->andWhere("e.competition = :competition")
            ->setParameter("competition", $competition)
            ->orderBy("e.startDate", "DESC")
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if (count($collection) > 0) {
            return $collection[0];
        }
        return null;
    }




// /**
//  * @return Season[] Returns an array of Season objects
//  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder("s")
            ->andWhere("s.exampleField = :val")
            ->setParameter("val", $value)
            ->orderBy("s.id", "ASC")
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Season
    {
        return $this->createQueryBuilder("s")
            ->andWhere("s.exampleField = :val")
            ->setParameter("val", $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
