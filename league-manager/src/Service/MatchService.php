<?php


namespace App\Service;


use App\Entity\Match\BasketballMatch;
use App\Entity\Match\FootballMatch;
use App\Entity\Match\Match;
use App\Entity\Season\Season;
use Doctrine\ORM\EntityManagerInterface;

class MatchService {

    const MIN_FOOTBALL_SCORE = "0";
    const MAX_FOOTBALL_SCORE = "6";
    const MIN_BASKETBALL_SCORE = "50";
    const MAX_BASKETBALL_SCORE = "120";


    private EntityManagerInterface $entityManager;
    private StandingsService $standingsService;
    private ScoreService $scoreService;

    public function __construct(EntityManagerInterface $entityManager,
                                StandingsService $standingsService, ScoreService $scoreService) {

        $this->entityManager = $entityManager;
        $this->standingsService = $standingsService;
        $this->scoreService = $scoreService;
    }

    /**
     * Updates next earliest match in the season
     * @param Season $season
     * @return Match|null returns null if there are no more matches to be played
     */
    public function updateNextInSeason(Season $season): ?Match {

        $match = $this->entityManager->getRepository(Match::class)->getNextInSeason($season);

        //if there is no more matches left to play in season
        if ($match === null) {
            return null;
        }

        if (is_a($match, FootballMatch::class)) {
            $homeScore = $this->scoreService->generateFootballScore();
            $awayScore = $this->scoreService->generateFootballScore();

        } else if (is_a($match, BasketballMatch::class)) {
            $homeScore = $this->scoreService->generateBasketballScore();
            $awayScore = $this->scoreService->generateBasketballScore();

            if ($homeScore->getFinal() === $awayScore->getFinal()) {
                $this->scoreService->generateBasketballOvertime($homeScore, $awayScore);
            }
        }

        $match->setHomeScore($homeScore);
        $match->setAwayScore($awayScore);
        $match->setStatus(Match::FINAL_);
        $match->setWinnerCode();

        $this->entityManager->flush();
        $this->standingsService->updateForOneMatch($match);

        return $match;
    }

    /**
     * Maps given array of matches based on their date.
     * Each key is date, and element is array of matches on that day
     * @param array $matches
     * @return array
     */
    public function groupByDate(array $matches): array {
        $grouped = [];
        foreach ($matches as $match) {
            $date = $match->getStartDate()->format("d.m.Y");

            if (in_array($date, array_keys($grouped))) {
                array_push($grouped[$date], $match);
            } else {
                $grouped[$date] = [$match];
            }
        }
        return $grouped;
    }

}