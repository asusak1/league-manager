<?php


namespace App\Controller\API;

use App\Entity\Season\Season;
use App\Entity\Standings\Standings;
use App\Entity\StandingsRow\StandingsRow;
use App\Form\SeasonType;
use App\Service\Helper\JsonHelper;
use App\Service\SeasonService;
use App\Service\StandingsService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SeasonController
 * @Route("/api/season")
 *
 * @package App\Controller
 */
class SeasonController extends AbstractController {

    /**
     * @Route("/{id}", name="season_edit", methods={"PUT"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, Season $season, SeasonService $seasonService) {

        $data = JsonHelper::getJson($request);

        $form = $this->createForm(SeasonType::class, $season, ["csrf_protection" => false]);
        $form->submit($data);

        if (!$form->isValid()) {
            $errors = $form->getErrors(true);
            throw new BadRequestHttpException($errors);
        }

        $season = $form->getData();

        if (!$seasonService->isValidDuration($season->getStartDate(), $season->getEndDate())) {
            throw new BadRequestHttpException("Season dates are not in valid range");
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($season);
        $entityManager->flush();

        return new JsonResponse(["message" => "Season updated"], Response::HTTP_OK);
    }

    /**
     * @Route("/{id}/standings", name="season_get_standings", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function getStandings(Season $season) {

        $standingsColl = $this->getDoctrine()->getRepository(Standings::class)->findBy(["season" => $season]);
        if (count($standingsColl) === 0) {
            throw new NotFoundHttpException("No standings exists for given Season ID");
        }

        return new JsonResponse($this->get("serializer")->serialize($standingsColl,
            "json", ["groups" => ["common"]]), Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/standings/{id}/rows", name="season_get_rows_for_standings", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function getRowsForStandings(Standings $standings) {

        $sportName = $standings->getSeason()->getCompetition()->getCategory()->getSport()->getName();

        if ($sportName === "football") {
            $standingsRows = $this->getDoctrine()->getRepository(StandingsRow::class)
                ->findBy(["standings" => $standings], ["points" => "DESC"]);
        } else if ($sportName === "basketball") {
            $standingsRows = $this->getDoctrine()->getRepository(StandingsRow::class)
                ->findBy(["standings" => $standings], ["winPercent" => "DESC"]);
        }

        if (count($standingsRows) === 0) {
            throw new NotFoundHttpException("No StandingsRows found for given Standings ID");
        }

        return new JsonResponse($this->get("serializer")->serialize($standingsRows,
            "json", ["groups" => ["standings_row_full", "competitor_full", "common"]]),
            Response::HTTP_OK, [], true);

    }

    /**
     * @Route("/{id}/recalculate-standings",name="season_recalculate_standings", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function recalculateStandings(Season $season, StandingsService $standingsService) {

        $standingsService->recalculateForSeason($season);

        return new JsonResponse(["message" => "Standings recalculated"], Response::HTTP_OK);

    }


}