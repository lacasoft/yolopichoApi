<?php

namespace Yolopicho\Utilities;

use Psr\Http\Message\ResponseInterface as Response;

class ApiResponseFormatter
{
    public static function formatResponse(Response $response, $data, int $statusCode): Response
    {
        /*foreach ($data as $object) {
            foreach ($object as $property => $value) {
                if (is_numeric($value)) {
                    $object->$property = (strpos($value, '.') !== false) ? (float)$value : (int)$value;
                }
            }
        }*/

        $formattedResponse = [
            'data' => $data,
            'statusCode' => $statusCode,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        $responseBody = json_encode($formattedResponse, JSON_NUMERIC_CHECK);
        $response->getBody()->write($responseBody);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}
