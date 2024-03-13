<?php

namespace Yolopicho\Utilities;

use Psr\Http\Message\ResponseInterface as Response;

class ApiResponseFormatter
{
    public static function formatResponse(Response $response, $data, int $statusCode): Response
    {
        $formattedResponse = [
            'data' => $data,
            'statusCode' => $statusCode,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $response->getBody()->write(json_encode($formattedResponse));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}
