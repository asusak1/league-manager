<?php


namespace App\Tests\Controller\API;


use App\Entity\Competitor\Competitor;
use App\Entity\Sport\Sport;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\Response;

class CompetitorControllerTest extends WebTestCase {

    protected KernelBrowser $client;
    private EntityManager $entityManager;

    public function setUp(): void {

        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get("doctrine")
            ->getManager();


        $application = new Application($kernel);
        $command = $application->find("app:initial-populate");
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
    }

    /**
     * @group haha
     */
    public function testEdit() {

        $this->client->request("GET", "");

        $competitor = $this->entityManager->getRepository(Competitor::class)->findOneBy([]);
        $sport = $this->entityManager->getRepository(Sport::class)->findOneBy([]);

        $name = "NEW TEST TEAM";
        $slug = "new-test-team";
        $countryISO = "US";

        // retrieve the admin user
        $user = $this->entityManager->getRepository(User::class)->findOneBy(["email" => "admin@admin.com"]);
        // simulate $user being logged in
        $this->client->loginUser($user);

        $this->client->request(
            "PUT",
            "/api/competitor/" . $competitor->getId(), [], [],
            ["CONTENT_TYPE" => "application/json"],
            sprintf('{
            "name": "%s",
            "slug": "%s",
            "sport": %d,
            "country": {"ISO": "%s"}
            }',
                $name, $slug, $sport->getId(), $countryISO)
        );
        $response = $this->client->getResponse();

        $this->entityManager->refresh($competitor);

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $updatedCompetitor = $this->entityManager->getRepository(Competitor::class)->findOneBy(["name" => $name]);

        self::assertNotNull($updatedCompetitor);

        self::assertEquals($sport, $updatedCompetitor->getSport());
        self::assertEquals($name, $updatedCompetitor->getName());
        self::assertEquals($countryISO, $updatedCompetitor->getCountry()->getISO());
    }


    public function testEditWithoutLogin() {

        $competitor = $this->entityManager->getRepository(Competitor::class)->findOneBy([]);
        $sport = $this->entityManager->getRepository(Sport::class)->findOneBy([]);

        $name = "NEW TEST TEAM";
        $slug = "new-test-team";
        $countryISO = "US";

        $this->client->request(
            "PUT",
            "/api/competitor/" . $competitor->getId(), [], [],
            ["CONTENT_TYPE" => "application/json"],
            sprintf('{
            "name": "%s",
            "slug": "%s",
            "sport": %d,
            "country": {"ISO": "%s"}
            }',
                $name, $slug, $sport->getId(), $countryISO)
        );
        $response = $this->client->getResponse();

        self::assertNotEquals(Response::HTTP_OK, $response->getStatusCode());

    }


    public function testGetLastFiveMatchesForAll() {
        $this->client->request("GET", "");

        // retrieve the normal user
        $user = $this->entityManager->getRepository(User::class)->findOneBy(["email" => "user@user.com"]);
        // simulate $user being logged in
        $this->client->loginUser($user);

        $this->client->request("GET", "/api/competitor/last-five-matches");

        $response = $this->client->getResponse();

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue($response->headers->contains("Content-Type", "application/json"));
        self::assertJson($response->getContent());

        $responseData = json_decode($response->getContent(), true);

        $competitors = $this->entityManager->getRepository(Competitor::class)->findAll();

        self::assertCount(count($competitors), $responseData);
    }


    public function testGetLastFiveMatchesForAllWithoutLogin() {
        $this->client->request("GET", "");

        $this->client->request("GET", "/api/competitor/last-five-matches");

        $response = $this->client->getResponse();

        self::assertNotEquals(Response::HTTP_OK, $response->getStatusCode());
    }
}


