<?php


namespace App\Controller;


use App\Entity\Category\Category;
use App\Entity\Competition\Competition;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CategoryController
 * @Route("/category")
 *
 * @package App\Controller
 */
class CategoryController extends AbstractController {

    /**
     * @Route("/{slug}/competitions", name="category_show_competitions", methods={"GET"})
     */
    public function showCompetitionsForCategory(Category $category) {
        $competitions = $this->getDoctrine()->getRepository(Competition::class)->findBy(["category" => $category]);

        $seasonUrls = [];
        foreach ($competitions as $competition) {
            $seasonUrls[] = $this->generateUrl("show_seasons_for_competition", [
                "slug" => $competition->getSlug(),
            ]);
        }

        return $this->render("competition/list.html.twig", [
            "category" => $category->getName(),
            "competitions" => $competitions,
            "links" => $seasonUrls
        ]);
    }

}