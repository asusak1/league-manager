<?php

namespace App\Controller\API;

use App\Entity\Competitor\Competitor;
use App\Entity\Match\Match;
use App\Form\CompetitorType;
use App\Service\Helper\JsonHelper;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use League\ISO3166\ISO3166;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CompetitorController
 * @Route("/api/competitor")
 *
 * @package App\Controller
 */
class CompetitorController extends AbstractFOSRestController {

    /**
     * @Route("/last-five-matches", name="competitor_get_last_5_matches_for_all", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function getLastFiveMatchesForAll() {

        $competitors = $this->getDoctrine()->getRepository(Competitor::class)->findAll();

        $competitorMatchMap = [];
        foreach ($competitors as $competitor) {
            $matches = $this->getDoctrine()->getRepository(Match::class)->findBy(
                ["homeCompetitor" => $competitor, "status" => Match::FINAL_], ["startDate" => "DESC"], 5);
            $competitorMatchMap[$competitor->getId()] = $matches;
        }

        return new JsonResponse($this->get("serializer")->serialize($competitorMatchMap, "json",
            ["groups" => ["common", "match_full"]]), Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/{id}", name="competitor_edit", methods={"PUT"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, Competitor $competitor) {

        $data = JsonHelper::getJson($request);

        $form = $this->createForm(CompetitorType::class, $competitor, ["csrf_protection" => false]);
        $form->submit($data);

        if (!$form->isValid()) {
            $errors = $form->getErrors(true);
            throw new BadRequestHttpException($errors);
        }

        $competitor = $form->getData();

        try {
            (new ISO3166())->alpha2($competitor->getCountry()->getISO());
        } catch (\Exception $e) {
            throw new BadRequestHttpException("Country ISO invalid");
        }

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($competitor);
        $entityManager->flush();

        return new JsonResponse(["message" => "Competitor updated"], Response::HTTP_OK);
    }
}


