<?php


namespace App\Command;


use App\Entity\Sport\Sport;
use App\Service\CategoryService;
use App\Service\CompetitionService;
use App\Service\Helper\DateTimeHelper;
use App\Service\Helper\StringHelper;
use App\Service\ScheduleGenerator;
use App\Service\SeasonService;
use App\Service\StandingsService;
use App\Service\TeamService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use SymfonyBundles\RedisBundle\Redis\ClientInterface;

/**
 * Populates database with initial data and creates schedule
 * Class InitialPopulateCommand
 * @package App\Command
 */
class InitialPopulateCommand extends Command {


    protected ClientInterface $client;
    protected ContainerInterface $container;

    protected static $defaultName = "app:initial-populate";

    protected \DateTime $minDate;
    protected \DateTime $maxDate;

    protected EntityManagerInterface $entityManager;
    protected SluggerInterface $slugger;
    protected CategoryService $categoryService;
    protected CompetitionService $competitionService;
    protected SeasonService $seasonService;
    protected TeamService $teamService;
    protected StandingsService $standingService;
    protected ScheduleGenerator $scheduleGenerator;
    protected int $numTeams;

    public function __construct(EntityManagerInterface $entityManager, SluggerInterface $slugger,
                                CategoryService $categoryService, CompetitionService $competitionService,
                                SeasonService $seasonService, TeamService $teamService,
                                StandingsService $standingsService, ScheduleGenerator $scheduleGenerator) {

        parent::__construct();

        $this->minDate = new \DateTime("2021-01-01");
        $this->maxDate = new \DateTime("2050-01-01");
        $this->numTeams = mt_rand(10, 16);

        $this->entityManager = $entityManager;
        $this->slugger = $slugger;
        $this->categoryService = $categoryService;
        $this->competitionService = $competitionService;
        $this->seasonService = $seasonService;
        $this->teamService = $teamService;
        $this->standingService = $standingsService;
        $this->scheduleGenerator = $scheduleGenerator;
    }


    protected function execute(InputInterface $input, OutputInterface $output) {

        $output->writeln("Populating database with generated initial data...");

        $sport = $this->entityManager->getRepository(Sport::class)->getRandom();

        $categoryName = StringHelper::random(5, 7);
        $category = $this->categoryService->create($categoryName, $sport);

        $competitionName = StringHelper::random(5, 7);
        $competition = $this->competitionService->create($competitionName, $category, 2);

        $start = DateTimeHelper::random($this->minDate, $this->maxDate);
        $end = (clone $start)->modify("+" . mt_rand(210, 330) . " days");
        $season = $this->seasonService->create($start, $end, $competition);

        $teams = [];
        for ($i = 0; $i < $this->numTeams; $i++) {
            $teams[] = $this->teamService->create("Team #" . mt_rand(100, 999), $sport, "HR");
        }

        $standings = $this->standingService->create($season, $teams);

        //generate schedule with total standings
        $this->scheduleGenerator->generate($standings[0]);

        $output->writeln("Finished!");

        return 0;
    }

}