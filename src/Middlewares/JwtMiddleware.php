<?php

namespace Yolopicho\Middlewares;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use \Slim\Psr7\Response as SlimResponse;

class JwtMiddleware implements MiddlewareInterface
{
    private $secretKey;
    private $algorithm;

    public function __construct() {
        $this->secretKey = getenv('JWT_SECRET_KEY');
        $this->algorithm = getenv('ALGORITHM');
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $token = $this->getTokenFromHeader($request);

        if (!$token) {
            return $this->respondWithError('Unauthorized. Token not provided.', 401);
        }

        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            $storeId = $decoded->storeId;
            $request = $request->withAttribute('storeIdToken', $storeId);
        } catch (\Exception $e) {
            return $this->respondWithError('Unauthorized. Invalid token.', 401);
        }

        return $handler->handle($request);
    }

    protected function getTokenFromHeader(Request $request): ?string
    {
        $header = $request->getHeaderLine('Authorization');
        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
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