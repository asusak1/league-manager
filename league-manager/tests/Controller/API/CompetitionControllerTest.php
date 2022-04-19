<?php


namespace App\Tests\Controller\API;


use App\Entity\Category\Category;
use App\Entity\Competition\Competition;
use App\Entity\Season\Season;
use App\Entity\User;
use App\Service\CompetitorService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\Response;

class CompetitionControllerTest extends WebTestCase {

    protected KernelBrowser $client;
    private EntityManager $entityManager;
    private CompetitorService $competitorService;


    public function setUp(): void {

        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get("doctrine")
            ->getManager();

        $this->competitorService = self::$container->get("App\Service\CompetitorService");


        $application = new Application($kernel);
        $command = $application->find("app:initial-populate");
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
    }


    public function testEdit() {

        $this->client->request("GET", ""); //dirty fix for weird bug

        $competition = $this->entityManager->getRepository(Competition::class)->findOneBy([]);

        $name = "NEW TEST COMPETITION";
        $slug = "new-test-competition";
        $category = $this->entityManager->getRepository(Category::class)->findOneBy([]);
        $matchesAgainst = 2;

        // retrieve the admin user
        $user = $this->entityManager->getRepository(User::class)->findOneBy(["email" => "admin@admin.com"]);
        // simulate $user being logged in
        $this->client->loginUser($user);

        $response = $this->client->request(
            'PUT',
            '/api/competition/' . $competition->getId(), [], [],
            ['CONTENT_TYPE' => 'application/json'],
            sprintf('{
            "name": "%s",
            "slug": "%s",
            "category": %d,
            "matchesAgainst": %d
            }',
                $name, $slug, $category->getId(), $matchesAgainst)
        );
        $response = $this->client->getResponse();

        $this->entityManager->refresh($competition);

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $updatedCompetition = $this->entityManager->getRepository(Competition::class)->findOneBy(["name" => $name]);

        self::assertNotNull($updatedCompetition);

        self::assertEquals($category, $updatedCompetition->getCategory());
        self::assertEquals($matchesAgainst, $updatedCompetition->getMatchesAgainst());
    }


    public function testEditWithoutLogin() {

        $this->client->request("GET", "");

        $competition = $this->entityManager->getRepository(Competition::class)->findOneBy([]);

        $name = "NEW TEST COMPETITION";
        $slug = "new-test-competition";
        $category = $this->entityManager->getRepository(Category::class)->findOneBy([]);
        $matchesAgainst = 2;

        $response = $this->client->request(
            "PUT",
            "/api/competition/" . $competition->getId(), [], [],
            ["CONTENT_TYPE' => 'application/json"],
            sprintf('{
            "name": "%s",
            "slug": "%s",
            "category": %d,
            "matchesAgainst": %d
            }',
                $name, $slug, $category->getId(), $matchesAgainst)
        );
        $response = $this->client->getResponse();

        self::assertNotEquals(Response::HTTP_OK, $response->getStatusCode());
    }


    public function testCreateNewSeason() {

        $this->client->request("GET", "");

        $season = $this->entityManager->getRepository(Season::class)->findOneBy([]);

        self::assertNotNull($season);

        $competitors = $this->competitorService->getCompetitorsForSeason($season);
        $competition = $season->getCompetition();

        $competitorsIds = [];
        foreach ($competitors as $competitor) {
            $competitorsIds[] = $competitor->getId();
        }

        $this->client->request(
            "POST",
            "/api/competition/" . $competition->getId() . "/new-season", [], [],
            ["CONTENT_TYPE" => "application/json"],
            sprintf('{
            "competitors": [%s]}',
                implode(",", $competitorsIds))
        );

        $response = $this->client->getResponse();
        //response should not be OK, because ROLE_ADMIN is required for this action
        self::assertNotEquals(Response::HTTP_OK, $response->getStatusCode());

        // retrieve the admin user
        $user = $this->entityManager->getRepository(User::class)->findOneBy(["email" => "admin@admin.com"]);
        // simulate $user being logged in
        $this->client->loginUser($user);

        $this->client->request(
            "POST",
            "/api/competition/" . $competition->getId() . "/new-season", [], [],
            ["CONTENT_TYPE" => "application/json"],
            sprintf('{
            "competitors": [%s]}',
                implode(",", $competitorsIds))
        );

        $response = $this->client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }


}


