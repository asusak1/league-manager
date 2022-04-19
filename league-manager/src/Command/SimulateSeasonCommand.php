<?php


namespace App\Command;


use App\Entity\Season\Season;
use App\Service\MatchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyBundles\RedisBundle\Redis\ClientInterface;

/**
 * Simulates whole season with random match results, and
 * updates Standings accordingly
 * Takes one argument which is ID of the season
 *
 * Class SimulateSeasonCommand
 * @package App\Command
 */
class SimulateSeasonCommand extends Command {


    protected ClientInterface $client;
    protected ContainerInterface $container;

    protected static $defaultName = "app:simulate-season";

    protected EntityManagerInterface $entityManager;
    protected MatchService $matchService;

    public function __construct(EntityManagerInterface $entityManager,
                                MatchService $matchService) {

        parent::__construct("app:simulate-season");

        $this->entityManager = $entityManager;
        $this->matchService = $matchService;
    }

    protected function configure() {
        $this
            // configure an argument
            ->addArgument("seasonId", InputArgument::REQUIRED, "ID of the season");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $seasonId = ($input->getArgument("seasonId"));

        if (!ctype_digit($seasonId)) {
            $output->writeln("Error: Season ID must be integer");
            return 1;
        }

        $season = $this->entityManager->getRepository(Season::class)->find($seasonId);

        if (!$season) {
            $output->writeln("Error: No season found with ID: $seasonId");
            return 1;
        }

        $output->writeln("Simulating all matches in the season...");

        while ($this->matchService->updateNextInSeason($season)) {
            continue;
        }

        $output->writeln("Finished!");
        return 0;
    }

}