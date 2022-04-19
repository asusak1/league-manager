<?php


namespace App\Controller;


use App\Entity\Competition\Competition;
use App\Entity\Season\Season;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CompetitionController
 * @Route("/competition")
 * @package App\Controller
 */
class CompetitionController extends AbstractController {

    /**
     * @Route("/{slug}/seasons", name="competition_show_seasons", methods={"GET"})
     */
    public function showSeasonsForCompetition(Competition $competition) {

        $seasons = $this->getDoctrine()->getRepository(Season::class)->findBy(["competition" => $competition]);

        $standingsUrl = [];
        foreach ($seasons as $season) {
            $standingsUrl[] = $this->generateUrl("show_standings_for_season", [
                "id" => $season->getId(),
            ]);
        }

        return $this->render("season/list.html.twig", [
            "competition" => $competition->getName(),
            "seasons" => $seasons,
            "links" => $standingsUrl
        ]);
    }

}