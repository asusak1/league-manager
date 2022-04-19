<?php


namespace App\Tests\Controller\API;


use App\Entity\Competition\Competition;
use App\Entity\Season\Season;
use App\Entity\Standings\Standings;
use App\Entity\User;
use App\Service\StandingsService;
use DateTime;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\Response;

class SeasonControllerTest extends WebTestCase {

    protected KernelBrowser $client;
    private EntityManager $entityManager;
    private StandingsService $standingsService;

    public function setUp(): void {

        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get("doctrine")
            ->getManager();

        $this->standingsService = self::$container->get("App\Service\StandingsService");


        $application = new Application($kernel);
        $command = $application->find("app:initial-populate");
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $season = $this->entityManager->getRepository(Season::class)->findOneBy([]);

        $command = $application->find("app:simulate-season");
        $commandTester = new CommandTester($command);
        $commandTester->execute(["seasonId" => $season->getId()]);


    }

    public function testEdit() {

        $this->client->request("GET", "");

        $season = $this->entityManager->getRepository(Season::class)->findOneBy([]);

        $name = "NEW TEST COMPETITION";
        $competition = $this->entityManager->getRepository(Competition::class)->findOneBy([]);
        $startDate = new DateTime();
        $endDate = (clone $startDate)->modify("+" . mt_rand(210, 330) . " days");

        // retrieve the admin user
        $user = $this->entityManager->getRepository(User::class)->findOneBy(["email" => "admin@admin.com"]);
        // simulate $user being logged in
        $this->client->loginUser($user);

        $this->client->request(
            "PUT",
            "/api/season/" . $season->getId(), [], [],
            ["CONTENT_TYPE" => "application/json"],
            sprintf('{
            "name": "%s",
            "competition": %d,
            "startDate": "%s",
            "endDate": "%s"
            }',
                $name, $competition->getId(), $startDate->format("Y-m-d h:i:s"), $endDate->format("Y-m-d h:i:s"))
        );
        $response = $this->client->getResponse();

        $this->entityManager->refresh($season);

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $updatedSeason = $this->entityManager->getRepository(Season::class)->findOneBy(["name" => $name]);

        self::assertNotNull($updatedSeason);

        self::assertEquals($competition, $updatedSeason->getCompetition());
        self::assertEquals($startDate->format("Y-m-d h:i:s"), $updatedSeason->getStartDate()->format("Y-m-d h:i:s"));
        self::assertEquals($endDate->format("Y-m-d h:i:s"), $updatedSeason->getEndDate()->format("Y-m-d h:i:s"));
    }


    public function testEditWithoutLogin() {

        $this->client->request("GET", "");

        $season = $this->entityManager->getRepository(Season::class)->findOneBy([]);

        $name = "NEW TEST COMPETITION";
        $competition = $this->entityManager->getRepository(Competition::class)->findOneBy([]);
        $startDate = new DateTime();
        $endDate = (clone $startDate)->modify("+" . mt_rand(210, 330) . " days");

        $this->client->request(
            "PUT",
            "/api/season/" . $season->getId(), [], [],
            ["CONTENT_TYPE" => "application/json"],
            sprintf('{
            "name": "%s",
            "competition": %d,
            "startDate": "%s",
            "endDate": "%s"
            }',
                $name, $competition->getId(), $startDate->format("Y-m-d h:i:s"), $endDate->format("Y-m-d h:i:s"))
        );
        $response = $this->client->getResponse();

        self::assertNotEquals(Response::HTTP_OK, $response->getStatusCode());
    }


    public function testGetStandings() {

        $this->client->request("GET", "");

        $season = $this->entityManager->getRepository(Season::class)->findOneBy([]);

        // retrieve the normal user
        $user = $this->entityManager->getRepository(User::class)->findOneBy(["email" => "user@user.com"]);
        // simulate $user being logged in
        $this->client->loginUser($user);

        $this->client->request("GET", sprintf("/api/season/%d/standings", $season->getId()));

        $response = $this->client->getResponse();

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue($response->headers->contains("Content-Type", "application/json"));
        self::assertJson($response->getContent());

        $responseData = json_decode($response->getContent(), true);

        self::assertNotNull($responseData);
        //there should be 3 standings (total, home, away)
        self::assertCount(3, $responseData);

        foreach ($responseData as $el) {
            self::assertArrayHasKey("id", $el);
            self::assertArrayHasKey("type", $el);
        }
    }


    public function testGetStandingsWithoutLogin() {

        $this->client->request("GET", "");

        $season = $this->entityManager->getRepository(Season::class)->findOneBy([]);

        $this->client->request("GET", sprintf("/api/season/%d/standings", $season->getId()));

        $response = $this->client->getResponse();

        self::assertNotEquals(Response::HTTP_OK, $response->getStatusCode());

    }


    public function testGetRowsForStandings() {

        $this->client->request("GET", "");

        $standings = $this->entityManager->getRepository(Standings::class)->findOneBy(["type" => "total"]);

        // retrieve the normal user
        $user = $this->entityManager->getRepository(User::class)->findOneBy(["email" => "user@user.com"]);
        // simulate $user being logged in
        $this->client->loginUser($user);

        $this->client->request("GET", sprintf("api/season/standings/%d/rows", $standings->getId()));

        $response = $this->client->getResponse();

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue($response->headers->contains("Content-Type", "application/json"));
        self::assertJson($response->getContent());

        $responseData = json_decode($response->getContent(), true);

        self::assertNotNull($responseData);
    }


    /**
     * @group failing2
     */
    public function testGetRowsForStandingsWithoutLogin() {

        $this->client->request("GET", "");

        $standings = $this->entityManager->getRepository(Standings::class)->findOneBy(["type" => "total"]);

        $this->client->request("GET", sprintf("api/season/standings/%d/rows", $standings->getId()));

        $response = $this->client->getResponse();

        self::assertNotEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testRecalculateStandings() {

        $this->client->request("GET", ""); //                                                  WEIRD BEHAVOIUR!!!!!!!!!!!!
        $season = $this->entityManager->getRepository(Season::class)->findOneBy([]);

        self::assertNotNull($season);

        $this->client->request("GET", sprintf("/api/season/%d/recalculate-standings", $season->getId()));
        $response = $this->client->getResponse();

        //response should not be OK, because ROLE_ADMIN is required for this action
        self::assertNotEquals(Response::HTTP_OK, $response->getStatusCode());

        // retrieve the admin user
        $user = $this->entityManager->getRepository(User::class)->findOneBy(["email" => "admin@admin.com"]);
        // simulate $user being logged in
        $this->client->loginUser($user);

        $this->standingsService->recalculateForSeason($season);

        $this->client->request("GET", sprintf("/api/season/%d/recalculate-standings", $season->getId()));
        $response = $this->client->getResponse();

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }
}


