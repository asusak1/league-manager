<?php


namespace App\Service;


use App\App\Entity\NotFoundException;
use App\App\Service\Helper\SplitDateException;
use App\Entity\Match\Match;
use App\Entity\Standings\Standings;
use App\Service\Helper\DateTimeHelper;
use App\Service\Helper\MatchHelper;
use Doctrine\ORM\EntityManagerInterface;
use ScheduleBuilder;

class ScheduleGenerator {

    private EntityManagerInterface $entityManager;
    private CompetitorService $competitorService;

    public function __construct(EntityManagerInterface $entityManager, CompetitorService $competitorService) {

        $this->entityManager = $entityManager;
        $this->competitorService = $competitorService;
    }

    /**
     * Generates schedule of a season for given standings
     * @param Standings $standings
     * @throws SplitDateException if date range is too narrow for
     * proper schedule (at least 12 hours between games for each team)
     * @throws NotFoundException if no competitors for standings season
     */
    public function generate(Standings $standings) {

        $competitors = $this->competitorService->getCompetitorsForSeason($standings->getSeason());
        $numRounds = (($count = count($competitors)) % 2 === 0 ? $count - 1 : $count) * 2;

        $startDates = DateTimeHelper::splitDates($standings->getSeason()->getStartDate(),
            $standings->getSeason()->getEndDate(), $numRounds, 12);

        $scheduleBuilder = new ScheduleBuilder($competitors, $numRounds);
        $scheduleBuilder->shuffle(18);
        $schedule = $scheduleBuilder->build();

        $index = 0;

        foreach ($schedule as $round => $matchups) {
            $startDate = $startDates[$index++];
            foreach ($matchups as $matchup) {
                //in case there is odd number of teams, every round one team will not have match
                if ($matchup[0] === null or $matchup[1] === null) {
                    continue;
                }
                $matchClass = MatchHelper::matchClassForSport($matchup[0]->getSport());

                $match = new $matchClass(); //e.g. new FootballMatch() or new BasketballMatch
                $match->setHomeCompetitor($matchup[0]);
                $match->setAwayCompetitor($matchup[1]);
                $match->setStartDate($startDate);
                $match->setStatus(Match::NOT_STARTED);
                $match->setCompetition($standings->getSeason()->getCompetition());
                $match->setSeason($standings->getSeason());

                $this->entityManager->persist($match);
                $this->entityManager->flush();
            }
        }
    }


}