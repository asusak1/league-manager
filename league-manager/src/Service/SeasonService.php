<?php


namespace App\Service;


use App\App\Entity\NotFoundException;
use App\Entity\Competition\Competition;
use App\Entity\Season\Season;
use App\Entity\Standings\Standings;
use App\Service\Helper\CommonHelper;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class SeasonService {

    private EntityManagerInterface $entityManager;
    private StandingsService $standingsService;
    private ScheduleGenerator $scheduleGenerator;
    private CompetitorService $competitorService;

    public function __construct(EntityManagerInterface $entityManager, StandingsService $standingsService,
                                ScheduleGenerator $scheduleGenerator, CompetitorService $competitorService) {

        $this->entityManager = $entityManager;
        $this->standingsService = $standingsService;
        $this->scheduleGenerator = $scheduleGenerator;
        $this->competitorService = $competitorService;
    }

    /**
     * Creates new season by finding latest previous season
     * for given competition and creates standings schedule for it.
     * If given $competitors array is not given, competitors from the
     * previous season will be used.
     *
     * @param Competition $competition existing competition
     * @param DateTime $start start date of the new season
     * @param DateTime $end end date of the new season
     * @param array $competitors array of competitors for new season
     * @return Season
     * @throws NotFoundException if latest season doesn't have any standings
     */
    public function createFromPrevSeason(Competition $competition, DateTime $start, DateTime $end, array $competitors = []): Season {

        $latestSeason = $this->entityManager->getRepository(Season::class)->getLatestForCompetition($competition);
        $standingsTotal = $this->entityManager->getRepository(Standings::class)->findOneBy(["season" => $latestSeason, "type" => "total"]);

        if ($standingsTotal === null) {
            throw new NotFoundException("Latest season for competition doesn't have total standings yet");
        }

        if (count($competitors) === 0) {
            $competitors = $this->competitorService->getCompetitorsForSeason($latestSeason);
        }

        $newSeason = $this->create($start, $end, $competition);

        $this->entityManager->persist($newSeason);
        $this->entityManager->flush();

        $standings = $this->standingsService->create($newSeason, $competitors);
        $this->scheduleGenerator->generate($standings[0]);

        return $newSeason;
    }

    /**
     * Creates and saves the Season object
     * @param DateTime $start start date of the season
     * @param DateTime $end end date of the season
     * @param Competition $competition
     * @return Season
     */
    public function create(DateTime $start, DateTime $end, Competition $competition): Season {
        $name = $this->generateName($start, $end);

        $season = new Season();
        $season->setName($name);
        $season->setCompetition($competition);
        $season->setStartDate($start);
        $season->setEndDate($end);
        $this->entityManager->persist($season);
        $this->entityManager->flush();

        return $season;
    }

    /**
     * Generates season name depending on the start and end dates
     * If season starts and ends in the same year, name will be e.g. "Season 2020"
     * If seasons ends year after, name will be e.g. "Season 20/21"
     * @param DateTime $start start date of the season
     * @param DateTime $end end date of the season
     * @return string
     */
    public function generateName(DateTime $start, DateTime $end): string {
        if ($start->format("Y") === $end->format("Y")) {
            return "Season " . $start->format("Y");
        } else {
            return "Season " . substr($start->format("Y"), 2, 2) . "/" .
                substr($end->format("Y"), 2, 2);
        }
    }

    /**
     * Computes total number of matches in a season
     * @param int $numTeams number of teams in a season
     * @param int $matchesAgainst number of matches team has to play other team in a season
     * @return int total number of matches
     */
    public function countMatchesInSeason(int $numTeams, int $matchesAgainst): int {
        return (($numTeams - 1) * $matchesAgainst * $numTeams) / 2;
    }

    /**
     * Checks if dates of the season are in valid range
     * Season should not be shorter than 7 or longer than 11 month
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return bool
     */
    public function isValidDuration(DateTime $startDate, DateTime $endDate): bool {
        $minEndDate = (clone $startDate)->modify("+" . Season::MIN_LENGTH . " months");;
        $maxEndDate = (clone $startDate)->modify("+" . Season::MAX_LENGTH . " months");

        return CommonHelper::isInRange($endDate, $minEndDate, $maxEndDate);
    }


}