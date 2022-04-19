<?php


namespace App\Service\Helper;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class JsonHelper {

    /**
     * Decodes json and throws exception if it is not vlaid
     * @param Request $request
     * @return mixed
     */
    public static function getJson(Request $request) {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BadRequestHttpException("Invalid json");
        }

        return $data;
    }
}