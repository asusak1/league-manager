<?php


namespace App\Tests\Service;


use App\Entity\Category\Category;
use App\Entity\Competition\Competition;
use App\Entity\Competitor\Competitor;
use App\Entity\Season\Season;
use App\Entity\Standings\Standings;
use App\Service\Helper\CommonHelper;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class InitialPopulateCommandTest extends KernelTestCase {


    private EntityManager $entityManager;
    private Application $application;

    public function setUp(): void {
        $kernel = self::bootKernel();

        $this->application = new Application($kernel);

        $this->entityManager = $kernel->getContainer()
            ->get("doctrine")
            ->getManager();


    }

    public function testExecute() {

        $command = $this->application->find("app:initial-populate");
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);


        $categories = $this->entityManager->getRepository(Category::class)->findAll();
        $competitions = $this->entityManager->getRepository(Competition::class)->findAll();
        $seasons = $this->entityManager->getRepository(Season::class)->findAll();
        $teams = $this->entityManager->getRepository(Competitor::class)->findAll();
        $standingsCollection = $this->entityManager->getRepository(Standings::class)->findAll();

        self::assertCount(1, $categories);
        self::assertCount(1, $competitions);
        self::assertCount(1, $seasons);
        self::assertTrue(CommonHelper::isInRange(count($teams), 10, 16));
        self::assertCount(3, $standingsCollection);


        $category = $categories[0];
        $competition = $competitions[0];
        $season = $seasons[0];
        $standings = $standingsCollection[1];


        //names
        self::assertTrue(CommonHelper::isInRange(strlen($category->getName()), 5, 7));
        self::assertTrue(CommonHelper::isInRange(strlen($competition->getName()), 5, 7));
        //references
        self::assertEquals($category, $competition->getCategory());
        self::assertEquals($competition, $season->getCompetition());
        self::assertEquals($season, $standings->getSeason());
        self::assertEquals($competition, $standings->getSeason()->getCompetition());

    }
}