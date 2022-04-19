<?php


namespace App\Controller;

use App\Entity\Sport\Sport;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class IndexController
 * @Route("/")
 *
 * @package App\Controller
 */
class IndexController extends AbstractController {

    /**
     * @Route("", name="index", methods={"GET"})
     */
    public function index() {

        $sports = $this->getDoctrine()->getRepository(Sport::class)->findAll();
        $links = [];
        foreach ($sports as $sport) {
            $links[$sport->getName()] = $this->generateUrl("sport_show_categories", ["slug" => $sport->getSlug()]);
        }
        return $this->render("index.html.twig", [
            "links" => $links,
            "header" => "League Manager"
        ]);
    }

}