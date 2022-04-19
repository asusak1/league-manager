<?php


namespace App\Controller\API;


use App\App\Entity\NotFoundException;
use App\Entity\Competition\Competition;
use App\Entity\Competitor\Competitor;
use App\Form\CompetitionType;
use App\Service\Helper\DateTimeHelper;
use App\Service\Helper\JsonHelper;
use App\Service\SeasonService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CompetitionController
 * @Route("/api/competition")
 *
 * @package App\Controller
 */
class CompetitionController extends AbstractController {

    /**
     * @Route("/{id}", name="competition_edit", methods={"PUT"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, Competition $competition) {

        $data = JsonHelper::getJson($request);

        $form = $this->createForm(CompetitionType::class, $competition, ['csrf_protection' => false]);
        $form->submit($data);

        if (!$form->isValid()) {
            $errors = $form->getErrors(true);
            throw new BadRequestHttpException($errors);
        }

        $competition = $form->getData();

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($competition);
        $entityManager->flush();

        return new JsonResponse(["message" => "Competition updated"], Response::HTTP_OK);
    }


    /**
     * @Route("/{id}/new-season", name="competition_create_new_season", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function createNewSeason(Request $request, Competition $competition, SeasonService $seasonService) {

        $competitorIds = $request->get("competitors");

        if (count($competitorIds) < 10 or count($competitorIds) > 16) {
            throw new BadRequestHttpException("Invalid length of parameter competitors");
        }

        $competitors = [];
        foreach ($competitorIds as $competitorId) {
            $competitor = $this->getDoctrine()->getRepository(Competitor::class)->find($competitorId);
            if ($competitor === null) {
                throw new BadRequestHttpException("One of the given competitors doesn't exist");
            }
            $competitors[] = $competitor;
        }

        $minDate = new \DateTime("2021-01-01");
        $maxDate = new \DateTime("2050-01-01");

        $start = DateTimeHelper::random($minDate, $maxDate);
        $end = (clone $start)->modify("+" . mt_rand(210, 330) . " days");

        try {
            $newSeason = $seasonService->createFromPrevSeason($competition, $start, $end, $competitors);
        } catch (NotFoundException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new JsonResponse($this->get('serializer')->serialize($newSeason, 'json', ['groups' => ['common']]), Response::HTTP_OK, [], true);

    }
}