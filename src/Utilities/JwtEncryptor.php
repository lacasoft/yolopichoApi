<?php

namespace Yolopicho\Utilities;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtEncryptor {

    public static function generateJwtToken(string $userId, string $userEmail)
    {
        $issuedAt = time();
        $expiration = getenv('EXPIRATION');
        $expirationTime = $issuedAt + $expiration;
        $payload = array(
            'storeId' => $userId,
            'storeEmail' => $userEmail,
            'iat' => $issuedAt,
            'exp' => $expirationTime
        );
        $secretKey = getenv('JWT_SECRET_KEY');
        $algorithm = getenv('ALGORITHM');
        $jwt = JWT::encode($payload, $secretKey, $algorithm);
        return $jwt;
    }

}