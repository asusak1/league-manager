<?php


namespace App\Service;


use App\App\Entity\NotFoundException;
use App\Entity\Match\Match;
use App\Entity\Season\Season;
use App\Entity\Standings\Standings;
use App\Entity\StandingsRow\StandingsRow;
use App\Service\Helper\MatchHelper;
use Doctrine\ORM\EntityManagerInterface;

class StandingsService {

    private EntityManagerInterface $entityManager;
    private StandingsRowService $standingsRowService;

    public function __construct(EntityManagerInterface $entityManager, StandingsRowService $standingsRowService) {
        $this->entityManager = $entityManager;
        $this->standingsRowService = $standingsRowService;
    }

    /**
     * Creates three new Standings for given season, with appropriate StandingsRows
     * @param Season $season
     * @param array $competitors
     * @return array created standings
     */
    public function create(Season $season, array $competitors) {

        $types = ["total", "home", "away"];
        $standingsCollection = [];

        foreach ($types as $type) {
            $standings = new Standings();
            $standings->setSeason($season);
            $standings->setType($type);

            $standingsCollection[] = $standings;
            $this->entityManager->persist($standings);

            //create standings rows for each competitor
            foreach ($competitors as $competitor) {
                $this->standingsRowService->create($standings, $competitor);
            }
        }
        $this->entityManager->flush();

        return $standingsCollection;
    }

    /**
     * Recalculate all standings for given season
     * @param Season $season
     * @throws NotFoundException if there are no standings for given season
     */
    public function recalculateForSeason(Season $season) {

        $standingsColl = $this->entityManager->getRepository(Standings::class)->findBy(["season" => $season]);

        if (count($standingsColl) === 0) {
            throw new NotFoundException("No standings found for given season");
        }

        //reset standings
        foreach ($standingsColl as $standings) {
            foreach ($standings->getStandingsRows() as $standingsRow) {
                $this->standingsRowService->reset($standingsRow);
            }
        }

        $matches = $this->entityManager->getRepository(Match::class)->findBy(["season" => $season]);

        foreach ($matches as $match) {
            $this->updateForOneMatch($match);
        }
    }

    /**
     * Updates all Standings (Standings Rows) for one played matched
     * This method calls 3 other methods to handle each Standings
     * @param Match $match
     */
    public function updateForOneMatch(Match $match) {

        $standingsTotal = $this->entityManager->getRepository(Standings::class)->findOneBy(
            ["season" => $match->getSeason(), "type" => "total"]);

        $standingsHome = $this->entityManager->getRepository(Standings::class)->findOneBy(
            ["season" => $match->getSeason(), "type" => "home"]);

        $standingsAway = $this->entityManager->getRepository(Standings::class)->findOneBy(
            ["season" => $match->getSeason(), "type" => "away"]);


        $this->updateTotalStandingsForOneMatch($standingsTotal, $match);
        $this->updateHomeStandingsForOneMatch($standingsHome, $match);
        $this->updateAwayStandingsForOneMatch($standingsAway, $match);
    }

    /**
     * Updates total Standings
     * @param Standings $standingsTotal
     * @param Match $match
     */
    private function updateTotalStandingsForOneMatch(Standings $standingsTotal, Match $match) {

        $standingsRowHome = $this->entityManager->getRepository(
            StandingsRow::class)->findOneBy(["standings" => $standingsTotal, "competitor" => $match->getHomeCompetitor()]);
        $standingsRowAway = $this->entityManager->getRepository(
            StandingsRow::class)->findOneBy(["standings" => $standingsTotal, "competitor" => $match->getAwayCompetitor()]);


        $this->standingsRowService->updateStandingsRow($standingsRowHome, $match->getHomeScore()->getFinal(),
            $match->getAwayScore()->getFinal(), $match->getWinnerCode());
        $this->standingsRowService->updateStandingsRow($standingsRowAway, $match->getAwayScore()->getFinal(),
            $match->getHomeScore()->getFinal(), MatchHelper::invertWinnerCode($match->getWinnerCode()));
    }

    /**
     * Updates home Standings
     * @param Standings $standings
     * @param Match $match
     */
    private function updateHomeStandingsForOneMatch(Standings $standings, Match $match) {

        $standingsRow = $this->entityManager->getRepository(
            StandingsRow::class)->findOneBy(["standings" => $standings, "competitor" => $match->getHomeCompetitor()]);

        $this->standingsRowService->updateStandingsRow($standingsRow, $match->getHomeScore()->getFinal(),
            $match->getAwayScore()->getFinal(), $match->getWinnerCode());
    }

    /**
     * Updates Away standings
     * @param Standings $standings
     * @param Match $match
     */
    private function updateAwayStandingsForOneMatch(Standings $standings, Match $match) {

        $standingsRow = $this->entityManager->getRepository(
            StandingsRow::class)->findOneBy(["standings" => $standings, "competitor" => $match->getAwayCompetitor()]);

        $this->standingsRowService->updateStandingsRow($standingsRow, $match->getAwayScore()->getFinal(),
            $match->getHomeScore()->getFinal(), MatchHelper::invertWinnerCode($match->getWinnerCode()));
    }

}