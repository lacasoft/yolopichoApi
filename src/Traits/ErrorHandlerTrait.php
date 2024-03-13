<?php

namespace Yolopicho\Traits;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yolopicho\Utilities\ApiResponseFormatter;

trait ErrorHandlerTrait
{
    protected function withTryCatch(Request $request, Response $response, callable $operation): Response
    {
        try {
            $responseBody = call_user_func($operation);
            $code = $response->getStatusCode();
        } catch (\PDOException $e) {
            $responseBody = ['error' => 'Error al acceder a la base de datos: ' . $e->getMessage()];
            $code = 500;
        } catch (\Exception $e) {
            $responseBody = ['error' => 'Ocurrió un error: ' . $e->getMessage()];
            $code = $e->getCode();
        }

        return ApiResponseFormatter::formatResponse($response, $responseBody, $code);
    }
}