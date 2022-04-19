<?php


namespace App\Service;


use App\Entity\Category\Category;
use App\Entity\Sport\Sport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoryService {

    private EntityManagerInterface $entityManager;
    private SluggerInterface $slugger;

    public function __construct(EntityManagerInterface $entityManager, SluggerInterface $slugger) {
        $this->entityManager = $entityManager;
        $this->slugger = $slugger;
    }

    /**
     * Creates and saves the Category object
     * If object with the same name already exists, doesn't create new one,
     * but it returns the saved one
     * @param string $name name of the category
     * @param Sport $sport
     * @return Category
     */
    public function create(string $name, Sport $sport): Category {

        $category = $this->entityManager->getRepository(Category::class)->findOneByName($name);

        if ($category) {
            return $category;
        } else {
            $category = new Category();
            $category->setName($name);
            $category->setSlug($this->slugger->slug($name)->folded());
            $category->setSport($sport);
            $this->entityManager->persist($category);
            $this->entityManager->flush();
        }
        return $category;
    }

}