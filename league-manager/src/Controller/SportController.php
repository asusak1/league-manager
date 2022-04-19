<?php


namespace App\Controller;


use App\Entity\Category\Category;
use App\Entity\Sport\Sport;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SportDisplayController
 * @Route("/sport")
 *
 * @package App\Controller
 */
class SportController extends AbstractController {

    /**
     * @Route("/{slug}/categories", name="sport_show_categories", methods={"GET"})
     */
    public function showCategoriesForSport(Sport $sport) {

        $categories = $this->getDoctrine()->getRepository(Category::class)->findBy(["sport" => $sport]);

        $competitionUrls = [];
        foreach ($categories as $category) {
            $competitionUrls[] = $this->generateUrl("show_competitions_for_category", [
                "slug" => $category->getSlug(),
            ]);
        }

        return $this->render("category/list.html.twig", [
            "sport" => $sport->getName(),
            "categories" => $categories,
            "links" => $competitionUrls
        ]);

    }

}