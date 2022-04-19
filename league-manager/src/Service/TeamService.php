<?php


namespace App\Service;

use App\Entity\Competitor\Team;
use App\Entity\Country\Country;
use App\Entity\Sport\Sport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class TeamService {

    private EntityManagerInterface $entityManager;
    private SluggerInterface $slugger;

    public function __construct(EntityManagerInterface $entityManager, SluggerInterface $slugger) {
        $this->entityManager = $entityManager;
        $this->slugger = $slugger;
    }

    /**
     * Creates and saves new Team object
     * @param string $name name of the team
     * @param Sport $sport
     * @param string $countryISO two-letter ISO code
     * @return Team
     */
    public function create(string $name, Sport $sport, string $countryISO): Team {

        $team = new Team();
        $team->setName($name);
        $team->setSlug($this->slugger->slug($name)->folded());
        $team->setSport($sport);

        $country = new Country();
        $country->setISO($countryISO);

        $team->setCountry($country);
        $this->entityManager->persist($team);
        $this->entityManager->flush();

        return $team;
    }
}