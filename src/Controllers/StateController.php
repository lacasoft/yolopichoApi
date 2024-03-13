<?php

namespace Yolopicho\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Yolopicho\Models\StateModel as State;
use Yolopicho\Utilities\ApiResponseFormatter;
use Yolopicho\Traits\ErrorHandlerTrait;

class StateController
{
    use ErrorHandlerTrait;

    public function getStates(Request $request, Response $response): Response
    {
        return $this->withTryCatch($request, $response, function() {
            return State::fetchAll();
        });
    }
}
