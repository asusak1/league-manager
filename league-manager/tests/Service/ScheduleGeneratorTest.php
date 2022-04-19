<?php


namespace App\Tests\Service;

use App\Entity\Competition\Competition;
use App\Entity\Competitor\Competitor;
use App\Entity\Match\Match;
use App\Entity\Season\Season;
use App\Service\Helper\CommonHelper;
use App\Service\Helper\MatchHelper;
use App\Service\SeasonService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ScheduleGeneratorTest extends KernelTestCase {

    private EntityManager $entityManager;
    private SeasonService $seasonService;

    public function setUp(): void {
        self::bootKernel();

        $kernel = self::bootKernel();

        $application = new Application($kernel);

        $this->entityManager = $kernel->getContainer()
            ->get("doctrine")
            ->getManager();

        $this->seasonService = self::$container->get("App\Service\SeasonService");


        $command = $application->find("app:initial-populate");
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

    }

    /**
     * scheduleGenerator->generate() is called inside app:initial-populate command, no need to call it explicitly
     */
    public function testGenerate() {
        $competition = $this->entityManager->getRepository(Competition::class)->findOneBy([]);
        $season = $this->entityManager->getRepository(Season::class)->findOneBy(["competition" => $competition]);
        $competitors = $this->entityManager->getRepository(Competitor::class)->findAll();

        $numTeams = count($this->entityManager->getRepository(Competitor::class)->findAll([]));
        $matches = $this->entityManager->getRepository(Match::class)->findBy([], ["startDate" => "ASC"]);

        //check if first match starts on the same date as season
        self::assertEquals($season->getStartDate(), $matches[0]->getStartDate());
        //check if last match starts on the same date as season ends
        self::assertEquals($season->getEndDate(), end($matches)->getStartDate());


        foreach ($matches as $match) {
            self::assertEquals($season, $match->getSeason());
            self::assertEquals($competition, $match->getCompetition());
            self::assertContains($match->getHomeCompetitor(), $competitors);
            self::assertContains($match->getAwayCompetitor(), $competitors);
            //check if match start date is inside the date range of the season
            self::assertEquals(true, CommonHelper::isInRange($match->getStartDate(), $season->getStartDate(), $season->getEndDate()));
            self::assertTrue(is_a($match, MatchHelper::matchClassForSport($competition->getCategory()->getSport())));
            self::assertEquals(Match::NOT_STARTED, $match->getStatus());
        }

        //check number of matches in season
        self::assertCount($this->seasonService->countMatchesInSeason($numTeams, $competition->getMatchesAgainst()), $matches);
    }


}