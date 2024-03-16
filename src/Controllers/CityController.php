<?php

namespace Yolopicho\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

use Yolopicho\Models\CityModel as City;
use Yolopicho\Traits\ErrorHandlerTrait;

class CityController
{
    use ErrorHandlerTrait;

    public function getCities(Request $request, Response $response): Response
    {
        return $this->withTryCatch($request, $response, function() use ($request){

            $queryFilters = $request->getQueryParams();

            return City::fetchAll($queryFilters);
        });
    }

    public function getCitiesByState(Request $request, Response $response, array $args): Response
    {
        return $this->withTryCatch($request, $response, function() use ($args) {

            $stateId = $args["stateId"];

            $stateIdValidator = v::number()->validate($stateId);

            if (!$stateIdValidator) {
                throw new \Exception("El campo es inválido. Debe ser un valor numerico.", 400);
            }

            return City::fetchByStateId($stateId);
        });
    }
}
