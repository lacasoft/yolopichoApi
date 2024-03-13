<?php

namespace Yolopicho\Middlewares;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use \Slim\Psr7\Response as SlimResponse;

class ApiKeyMiddleware implements MiddlewareInterface
{
    private $secretKey;

    public function __construct() {
        $this->secretKey = getenv('API_KEY');
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $apikey = $this->getApiKeyFromHeader($request);

        if (!$apikey) {
            return $this->respondWithError('Unauthorized. apikey not provided.', 401);
        }

        if ($apikey != $this->secretKey) {
            return $this->respondWithError('Unauthorized. apikey not provided.', 401);
        }

        return $handler->handle($request);
    }

    protected function getApiKeyFromHeader(Request $request): ?string
    {
        $header = $request->getHeaderLine('x-api-key');
        if ($header) {
            return $header;
        }
        return null;
    }

    protected function respondWithError(string $message, int $statusCode): Response
    {
        $errorData = ['error' => $message];
        $response = new SlimResponse;
        $response->getBody()->write(json_encode($errorData, JSON_PRETTY_PRINT));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}