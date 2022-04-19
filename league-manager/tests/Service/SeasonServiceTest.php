<?php


namespace App\Tests\Service;

use App\Entity\Competition\Competition;
use App\Entity\Season\Season;
use App\Entity\Standings\Standings;
use App\Service\CompetitorService;
use App\Service\SeasonService;
use DateTime;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class SeasonServiceTest extends KernelTestCase {

    private SeasonService $seasonService;
    private CompetitorService $competitorService;
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

        $this->seasonService = self::$container->get("App\Service\SeasonService");
        $this->competitorService = self::$container->get("App\Service\CompetitorService");
    }

    public function testCreateFromPrevSeason() {
        $competition = $this->entityManager->getRepository(Competition::class)->findOneBy([]);
        $season = $this->entityManager->getRepository(Season::class)->findOneBy([]);

        $startDate = $season->getStartDate()->modify("+" . 1 . " year");
        $endDate = $season->getEndDate()->modify("+" . 1 . " year");

        $this->seasonService->createFromPrevSeason($competition, $startDate, $endDate);

        $newSeason = $this->entityManager->getRepository(Season::class)->findOneBy(
            ["startDate" => $startDate, "competition" => $competition]);

        self::assertNotNull($newSeason);

        //check if both seasons have same competition
        self::assertEquals($competition, $newSeason->getCompetition());

        $standingsColl = $this->entityManager->getRepository(Standings::class)->findBy(["season" => $newSeason]);

        //check if there are 3 standings for new season (total, home, away)
        self::assertCount(3, $standingsColl);

        $competitors = $this->competitorService->getCompetitorsForSeason($season);
        $competitorsNewSeason = $this->competitorService->getCompetitorsForSeason($newSeason);


        //check if both seasons have same competitors
        for ($i = 0; $i < count($competitors); $i++) {
            self::assertEquals($competitors[$i], $competitorsNewSeason[$i]);
        }

        //service for generating schedule is tested separately
    }

    public function testCountMatchesInSeason() {

        self::assertEquals(1, $this->seasonService->countMatchesInSeason(2, 1));
        self::assertEquals(2, $this->seasonService->countMatchesInSeason(2, 2));
        self::assertEquals(6, $this->seasonService->countMatchesInSeason(4, 1));
        self::assertEquals(3, $this->seasonService->countMatchesInSeason(3, 1));
        self::assertEquals(6, $this->seasonService->countMatchesInSeason(3, 2));
        self::assertEquals(156, $this->seasonService->countMatchesInSeason(13, 2));
    }

    /**
     * @group duration
     */
    public function testIsValidDuration() {

        $startDate = new DateTime();
        $endDate = (clone $startDate)->modify("+ 12 days");

        self::assertNotTrue($this->seasonService->isValidDuration($startDate, $endDate));

        $endDate = (clone $startDate)->modify("+ 12 months");

        self::assertNotTrue($this->seasonService->isValidDuration($startDate, $endDate));

        $endDate = (clone $startDate)->modify("+ 7 months");

        self::assertTrue($this->seasonService->isValidDuration($startDate, $endDate));

        $endDate = (clone $startDate)->modify("+ 11 months");

        self::assertTrue($this->seasonService->isValidDuration($startDate, $endDate));

    }

}