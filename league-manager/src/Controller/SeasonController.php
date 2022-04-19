<?php


namespace App\Controller;


use App\Entity\Match\Match;
use App\Entity\Season\Season;
use App\Entity\Standings\Standings;
use App\Entity\StandingsRow\StandingsRow;
use App\Service\MatchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SeasonController
 * @Route("/season")
 *
 * @package App\Controller
 */
class SeasonController extends AbstractController {

    /**
     * @Route("/{id}/standings", name="show_standings_for_season", methods={"GET"})
     */
    public function showStandingsForSeason(Season $season, MatchService $matchService) {

        $sport = $season->getCompetition()->getCategory()->getSport();
        $standings = $this->getDoctrine()->getRepository(Standings::class)
            ->findOneBy(["season" => $season, "type" => "total"]);

        if ($standings === null) {
            throw new NotFoundHttpException("No standings exists for given season ID");
        }

        switch ($sport->getName()) {
            case "football":
                $standingsRows = $this->getDoctrine()->getRepository(StandingsRow::class)->findBy
                (["standings" => $standings], ["points" => "DESC", "scoresFor" => "DESC"]);
                break;
            case "basketball":
                $standingsRows = $this->getDoctrine()->getRepository(StandingsRow::class)->findBy
                (["standings" => $standings], ["winPercent" => "DESC"]);
                break;
        }

        $matches = $this->getDoctrine()->getRepository(Match::class)->findBy(
            ["season" => $season, "status" => Match::FINAL_], ["startDate" => "ASC"]);

        $groupedMatches = $matchService->groupByDate($matches);

        return $this->render("standings/show.html.twig", [
            "standingsRows" => $standingsRows,
            "sport" => $sport,
            "competition" => $season->getCompetition(),
            "season" => $season,
            "groupedMatches" => $groupedMatches
        ]);
    }

}