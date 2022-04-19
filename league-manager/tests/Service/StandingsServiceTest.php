<?php


namespace App\Tests\Service;

use App\Entity\Match\Match;
use App\Entity\Season\Season;
use App\Entity\Standings\Standings;
use App\Entity\StandingsRow\StandingsRow;
use App\Service\MatchService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class StandingsServiceTest extends KernelTestCase {

    private MatchService $matchService;
    private EntityManager $entityManager;

    public function setUp(): void {

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $this->entityManager = $kernel->getContainer()
            ->get("doctrine")
            ->getManager();

        $command = $application->find("app:initial-populate");
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $this->matchService = self::$container->get("App\Service\MatchService");
    }

    public function testUpdateForOneMatch() {

        $season = $this->entityManager->getRepository(Season::class)->findOneBy([]);
        $nextMatch = $this->entityManager->getRepository(Match::class)->getNextInSeason($season);

        $standings = $this->entityManager->getRepository(Standings::class)->findOneBy(["season" => $season, "type" => "total"]);

        $standingsRowHomeCompetitor = $this->entityManager->getRepository(StandingsRow::class)->findOneBy(
            ["standings" => $standings, "competitor" => $nextMatch->getHomeCompetitor()]);

        $standingsRowAwayCompetitor = $this->entityManager->getRepository(StandingsRow::class)->findOneBy(
            ["standings" => $standings, "competitor" => $nextMatch->getAwayCompetitor()]);

        $totalMatchesHomeComp = $standingsRowHomeCompetitor->getMatches();
        $totalMatchesAwayComp = $standingsRowAwayCompetitor->getMatches();


        //check if next closest match has status NOT_STARTED
        self::assertEquals(Match::NOT_STARTED, $nextMatch->getStatus());
        self::assertEquals($season, $nextMatch->getSeason());

        $updatedMatch = $this->matchService->updateNextInSeason($season);

        self::assertEquals($totalMatchesHomeComp + 1, $standingsRowHomeCompetitor->getMatches());
        self::assertEquals($totalMatchesAwayComp + 1, $standingsRowAwayCompetitor->getMatches());

    }
}