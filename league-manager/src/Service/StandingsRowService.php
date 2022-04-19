<?php


namespace App\Service;


use App\Entity\Competitor\Competitor;
use App\Entity\Match\Match;
use App\Entity\Standings\Standings;
use App\Entity\StandingsRow\StandingsRow;
use App\Service\Helper\MatchHelper;
use Doctrine\ORM\EntityManagerInterface;

class StandingsRowService {

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * Creates StandingsRows for given Standings
     * @param Standings $standings
     * @param Competitor $competitor
     * @return StandingsRow
     */
    public function create(Standings $standings, Competitor $competitor) {

        $standingsRow = new StandingsRow();
        $standingsRow->setStandings($standings);
        $standingsRow->setCompetitor($competitor);

        switch ($competitor->getSport()->getName()) {
            case "football":
                $standingsRow->setDraws(0);
                $standingsRow->setPoints(0);
                break;
            case "basketball":
                $standingsRow->setWinPercent(0);
                break;
        }

        $this->entityManager->persist($standingsRow);
        $this->entityManager->flush();

        return $standingsRow;
    }

    /**
     * Updates single StandingsRow by calling appropriate method based on sport
     * @param StandingsRow $standingsRow
     * @param int $scoreFor
     * @param int $scoreAgainst
     * @param int $winnerCode
     */
    public function updateStandingsRow(StandingsRow $standingsRow, int $scoreFor, int $scoreAgainst, int $winnerCode) {

        $sport = $standingsRow->getCompetitor()->getSport();

        switch ($sport->getName()) {
            case "football":
                $this->updateStandingsRowFootball($standingsRow, $scoreFor, $scoreAgainst, $winnerCode);
                break;
            case "basketball":
                $this->updateStandingsRowBasketball($standingsRow, $scoreFor, $scoreAgainst, $winnerCode);
                break;
        }
    }

    /**
     * Updates single StandingsRow for football
     * @param StandingsRow $standingsRow
     * @param int $scoreFor
     * @param int $scoreAgainst
     * @param int $winnerCode
     */
    private function updateStandingsRowFootball(StandingsRow $standingsRow, int $scoreFor, int $scoreAgainst, int $winnerCode) {

        $standingsRow->setMatches($standingsRow->getMatches() + 1);
        $standingsRow->setScoresFor($standingsRow->getScoresFor() + $scoreFor);
        $standingsRow->setScoresAgainst($standingsRow->getScoresAgainst() + $scoreAgainst);

        switch ($winnerCode) {
            case Match::HOME_WIN:
                $standingsRow->setPoints($standingsRow->getPoints() + 3);
                $standingsRow->setWins($standingsRow->getWins() + 1);
                break;
            case Match::AWAY_WIN:
                $standingsRow->setLosses($standingsRow->getLosses() + 1);
                break;
            case Match::DRAW:
                $standingsRow->setPoints($standingsRow->getPoints() + 1);
                $standingsRow->setDraws($standingsRow->getDraws() + 1);
                break;
        }

        $this->entityManager->persist($standingsRow);
        $this->entityManager->flush();
    }

    /**
     * Updates single StandingsRow for basketball
     * @param StandingsRow $standingsRow
     * @param int $scoreFor
     * @param int $scoreAgainst
     * @param int $winnerCode
     */
    private function updateStandingsRowBasketball(StandingsRow $standingsRow,
                                                  int $scoreFor, int $scoreAgainst, int $winnerCode) {

        $standingsRow->setMatches($standingsRow->getMatches() + 1);
        $standingsRow->setScoresFor($standingsRow->getScoresFor() + $scoreFor);
        $standingsRow->setScoresAgainst($standingsRow->getScoresAgainst() + $scoreAgainst);

        switch ($winnerCode) {
            case Match::HOME_WIN:
                $standingsRow->setWins($standingsRow->getWins() + 1);
                break;
            case Match::AWAY_WIN:
                $standingsRow->setLosses($standingsRow->getLosses() + 1);
                break;
        }

        $standingsRow->setWinPercent(MatchHelper::computeWinPerc($standingsRow->getMatches(), $standingsRow->getWins()));

        $this->entityManager->persist($standingsRow);
        $this->entityManager->flush();

    }

    /**
     * Resets single StandingsRow, as if there are no matches played
     * @param StandingsRow $standingsRow
     */
    public function reset(StandingsRow $standingsRow) {

        $standingsRow->setMatches(0);
        $standingsRow->setWins(0);
        $standingsRow->setLosses(0);
        $standingsRow->setScoresFor(0);
        $standingsRow->setScoresAgainst(0);
        $standingsRow->setDraws(null);
        $standingsRow->setPoints(null);
        $standingsRow->setWinPercent(null);

        $this->entityManager->persist($standingsRow);
        $this->entityManager->flush();

    }

}