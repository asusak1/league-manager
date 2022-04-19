<?php


namespace App\Command;


use App\Entity\Season\Season;
use App\Service\StandingsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyBundles\RedisBundle\Redis\ClientInterface;

/**
 * Recalculates standings for season if the final score
 * is changed after the match finished
 *
 * Class RecalculateStandingsCommand
 * @package App\Command
 */
class RecalculateStandingsCommand extends Command {

    public const CHANGED_FINAL_SCORE = "RECOMPUTE:STANDINGS";

    protected ClientInterface $client;
    protected ContainerInterface $container;

    protected static $defaultName = "app:watch-final-score-change";

    protected StandingsService $standingService;
    protected EntityManagerInterface $entityManager;


    public function __construct(ClientInterface $client, ContainerInterface $container,
                                StandingsService $standingsService, EntityManagerInterface $entityManager) {
        parent::__construct();
        $this->client = $client;
        $this->container = $container;
        $this->standingService = $standingsService;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        while (true) {
            $seasonId = $this->client->pop(self::CHANGED_FINAL_SCORE);
            if ($seasonId) {

                $output->writeln("Score on match has been changed after it has finished,
                 recalculating standings of the whole season with ID: $seasonId");

                $season = $this->entityManager->getRepository(Season::class)->find($seasonId);
                $this->standingService->recalculateForSeason($season);
                continue;
            }
            sleep(1);
        }
    }

}