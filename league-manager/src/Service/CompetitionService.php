<?php


namespace App\Service;


use App\Entity\Category\Category;
use App\Entity\Competition\Competition;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class CompetitionService {

    private EntityManagerInterface $entityManager;
    private SluggerInterface $slugger;

    public function __construct(EntityManagerInterface $entityManager, SluggerInterface $slugger) {
        $this->entityManager = $entityManager;
        $this->slugger = $slugger;
    }

    /**
     * Creates and saves the Competition object
     * If object with the same name already exists, doesn't create new one,
     * but it returns the saved one
     * @param string $name name of the competition
     * @param Category $category
     * @param int $matchesAgainst how many times competitors play each other
     * @return Competition
     */
    public function create(string $name, Category $category, int $matchesAgainst): Competition {

        $competition = $this->entityManager->getRepository(Competition::class)->findOneByName($name);

        if ($competition) {
            return $competition;
        } else {
            $competition = new Competition();
            $competition->setName($name);
            $competition->setSlug($this->slugger->slug($name)->folded());
            $competition->setCategory($category);
            $competition->setMatchesAgainst($matchesAgainst);

            $this->entityManager->persist($competition);
            $this->entityManager->flush();
        }
        return $competition;

    }

}