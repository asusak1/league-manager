<?php


namespace App\Tests\Service;

use App\Entity\Match\BasketballMatch;
use App\Entity\Match\FootballMatch;
use App\Entity\Match\Match;
use App\Entity\Season\Season;
use App\Service\CompetitorService;
use App\Service\Helper\CommonHelper;
use App\Service\MatchService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class MatchServiceTest extends KernelTestCase {

    private MatchService $matchService;
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

        $this->matchService = self::$container->get("App\Service\MatchService");
    }

    public function testUpdateNextInSeason() {

        $season = $this->entityManager->getRepository(Season::class)->findOneBy([]);
        $nextMatch = $this->entityManager->getRepository(Match::class)->getNextInSeason($season);

        //check if next closest match has status NOT_STARTED
        self::assertEquals(Match::NOT_STARTED, $nextMatch->getStatus());
        self::assertEquals($season, $nextMatch->getSeason());

        $updatedMatch = $this->matchService->updateNextInSeason($season);

        self::assertEquals($nextMatch, $updatedMatch);
        self::assertEquals(Match::FINAL_, $updatedMatch->getStatus());

        if (is_a($updatedMatch, FootballMatch::class)) {
            self::assertNotNull($updatedMatch->getHomeScore()->getHalftime());
            self::assertNotNull($updatedMatch->getAwayScore()->getHalftime());

            self::assertTrue(CommonHelper::isInRange($updatedMatch->getHomeScore()->getFinal(),
                MatchService::MIN_FOOTBALL_SCORE, MatchService::MAX_FOOTBALL_SCORE));

            self::assertTrue(CommonHelper::isInRange($updatedMatch->getAwayScore()->getFinal(),
                MatchService::MIN_FOOTBALL_SCORE, MatchService::MAX_FOOTBALL_SCORE));
        }

        if (is_a($updatedMatch, BasketballMatch::class)) {
            self::assertNotNull($updatedMatch->getHomeScore()->getPeriod1());
            self::assertNotNull($updatedMatch->getHomeScore()->getPeriod2());
            self::assertNotNull($updatedMatch->getHomeScore()->getPeriod3());
            self::assertNotNull($updatedMatch->getHomeScore()->getPeriod4());

            self::assertNotNull($updatedMatch->getAwayScore()->getPeriod1());
            self::assertNotNull($updatedMatch->getAwayScore()->getPeriod2());
            self::assertNotNull($updatedMatch->getAwayScore()->getPeriod3());
            self::assertNotNull($updatedMatch->getAwayScore()->getPeriod4());

            self::assertTrue(CommonHelper::isInRange($updatedMatch->getHomeScore()->getFinal(),
                MatchService::MIN_BASKETBALL_SCORE, MatchService::MAX_BASKETBALL_SCORE));

            self::assertTrue(CommonHelper::isInRange($updatedMatch->getAwayScore()->getFinal(),
                MatchService::MIN_BASKETBALL_SCORE, MatchService::MAX_BASKETBALL_SCORE));

        }
    }

}