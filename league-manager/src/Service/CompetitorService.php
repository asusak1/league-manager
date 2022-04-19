<?php


namespace App\Service;


use App\App\Entity\NotFoundException;
use App\Entity\Season\Season;
use App\Entity\Standings\Standings;
use App\Entity\StandingsRow\StandingsRow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class CompetitorService {

    private EntityManagerInterface $entityManager;
    private SluggerInterface $slugger;

    public function __construct(EntityManagerInterface $entityManager, SluggerInterface $slugger) {
        $this->entityManager = $entityManager;
        $this->slugger = $slugger;
    }

    /** Finds all competitors in given season
     * @param Season $season
     * @return array
     * @throws NotFoundException if standings or rows doesn't exist for given season
     */
    public function getCompetitorsForSeason(Season $season): array {

        $standings = $this->entityManager->getRepository(Standings::class)->findOneBy(["season" => $season, "type" => "total"]);
        if ($standings === null) {
            throw new NotFoundException("Standings for given season not found");
        }

        $standingsRows = $this->entityManager->getRepository(StandingsRow::class)->findBy(["standings" => $standings], ["id" => "ASC"]);

        if (count($standingsRows) === 0) {
            throw new NotFoundException("Standing rows for given season not found");
        }


        $competitors = [];
        foreach ($standingsRows as $standingsRow) {
            $competitors[] = $standingsRow->getCompetitor();
        }

        return $competitors;

    }

}