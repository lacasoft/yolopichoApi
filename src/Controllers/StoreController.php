<?php

namespace Yolopicho\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Respect\Validation\Validator as v;

use Yolopicho\Models\StoreModel as Store;
use Yolopicho\Traits\ErrorHandlerTrait;

class StoreController
{
    use ErrorHandlerTrait;

    public function getStores(Request $request, Response $response, array $args)
    {
        return $this->withTryCatch($request, $response, function() use ($request, $args) {

            $data = $request->getParsedBody();
            $storeId = $args["storeId"];

            $storeIdToken = $request->getAttribute('storeIdToken');
            if($storeIdToken != $storeId){
                throw new \Exception("no puedes modificar otro perfil", 400);
            }

            return $commerces = Store::fetchById($storeId);
        });

        try {

            $response->getBody()->write(json_encode($commerces));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } catch (\Exception $e) {
            $error = [
                "message" => $e->getMessage()
            ];

            $response->getBody()->write(json_encode($error));
            return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
        }
    }

    public function getCommerceByName(Request $request, Response $response, array $args)
    {
        try {
            $name = $args["name"];

            $commerces = Store::fetchByName($name);
            $response->getBody()->write(json_encode($commerces));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } catch (\Exception $e) {
            $error = [
                "message" => $e->getMessage()
            ];

            $response->getBody()->write(json_encode($error));
            return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
        }
    }

    public function getCommerceByCity(Request $request, Response $response, array $args)
    {
        try {
            $cityName = $args["cityName"];

            $commerces = Store::fetchByCityName($cityName);
            $response->getBody()->write(json_encode($commerces));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } catch (\Exception $e) {
            $error = [
                "message" => $e->getMessage()
            ];

            $response->getBody()->write(json_encode($error));
            return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
        }
    }

    public function getCommerceByCategoty(Request $request, Response $response, array $args)
    {
        try {
            $categoryName = $args["categoryName"];

            $commerces = Store::fetchByCategoryName($categoryName);
            $response->getBody()->write(json_encode($commerces));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } catch (\Exception $e) {
            $error = [
                "message" => $e->getMessage()
            ];

            $response->getBody()->write(json_encode($error));
            return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
        }
    }

    public function addStore(Request $request, Response $response)
    {
        return $this->withTryCatch($request, $response, function() use ($request) {

            $data = $request->getParsedBody();

            if (!isset($data["name"], $data["categoryId"], $data["email"], $data["password"], $data["phone"], $data["streetAddress"], $data["streetNumber"], $data["cityId"], $data["stateId"])) {
                throw new \Exception("Se requieren todos los campos",400);
            }

            $name = $data["name"];
            $categoryId = $data["categoryId"];
            $email = $data["email"];
            $password = $data["password"];
            $phone = $data["phone"];
            $streetAddress = $data["streetAddress"];
            $streetNumber = $data["streetNumber"];
            $streetIntNumber = isset($data["streetIntNumber"]) ? $data["streetIntNumber"] : '';
            $cityId = $data["cityId"];
            $stateId = $data["stateId"];

            $errors = [];

            if (!v::notEmpty()->stringType()->length(2, 255)->validate($name)) {
                $errors[] = "El nombre debe tener entre 2 y 255 caracteres.";
            }
            if (!v::number()->validate($categoryId)) {
                $errors[] = "El ID de categoría debe ser numérico.";
            }
            if (!v::notEmpty()->email()->validate($email)) {
                $errors[] = "El correo electrónico es inválido.";
            }
            if (!v::notEmpty()->stringType()->length(8, 255)->validate($password)) {
                $errors[] = "La contraseña debe tener entre 8 y 255 caracteres.";
            }
            if (!v::notEmpty()->stringType()->length(10, null)->validate($phone)) {
                $errors[] = "El teléfono debe tener 10 caracteres.";
            }
            if (!v::notEmpty()->stringType()->length(2, 255)->validate($streetAddress)) {
                $errors[] = "La dirección debe tener entre 2 y 255 caracteres.";
            }
            if (!v::notEmpty()->stringType()->validate($streetNumber)) {
                $errors[] = "El número es requerido";
            }
            if (!v::number()->validate($cityId)) {
                $errors[] = "El ID de ciudad debe ser numérico.";
            }
            if (!v::number()->validate($stateId)) {
                $errors[] = "El ID de estado debe ser numérico.";
            }

            if (!empty($errors)) {
                $errorMessage = implode(", ", $errors);
                throw new \Exception($errorMessage,400);
            }

            $aesPassword = $this->encryptAES($password);

            return Store::add($name, $categoryId, $email, $aesPassword, $phone, $streetAddress, $streetNumber, $streetIntNumber, $cityId, $stateId);
        });
    }

    public function loginStore(Request $request, Response $response)
    {
        return $this->withTryCatch($request, $response, function() use ($request) {
            $data = $request->getParsedBody();

            if (!isset($data["email"], $data["password"])) {
                throw new \Exception("Se requieren todos los campos", 400);
            }

            $email = $data["email"] ?? '';
            $password = $data["password"] ?? '';

            if (!v::notEmpty()->email()->validate($email)) {
                $errors[] = "El correo electrónico es inválido.";
            }
            if (!v::notEmpty()->stringType()->length(8, 255)->validate($password)) {
                $errors[] = "La contraseña debe tener entre 8 y 255 caracteres.";
            }

            if (!empty($errors)) {
                $errorMessage = implode(", ", $errors);
                throw new \Exception($errorMessage,400);
            }

            $aesPassword = $this->encryptAES($password);

            $store = Store::login($email);

            if ($store->passwordHash != $aesPassword) {
                throw new \Exception("No se encuentra el comercio",400);
            }

            $token = $this->generateJwtToken($store->id, $store->email);

            return ['token' => $token];
        });
    }

    public function updateStore(Request $request, Response $response, array $args)
    {
        return $this->withTryCatch($request, $response, function() use ($request, $args) {

            $data = $request->getParsedBody();
            $storeId = $args["storeId"];

            $storeIdToken = $request->getAttribute('storeIdToken');
            if($storeIdToken != $storeId){
                throw new \Exception("no puedes modificar otro perfil",400);
            }

            if($data == []){
                throw new \Exception("nada que actaulizar",400);
            }

            $name = isset($data["name"]) ? $data["name"] : '';
            $categoryId = isset($data["categoryId"]) ? $data["categoryId"] : 0;
            $phone = isset($data["phone"]) ? $data["phone"] : '';
            $streetAddress = isset($data["streetAddress"]) ? $data["streetAddress"] : '';
            $streetNumber = isset($data["streetNumber"]) ? $data["streetNumber"] : '';
            $streetIntNumber = isset($data["streetIntNumber"]) ? $data["streetIntNumber"] : '';
            $cityId = isset($data["cityId"]) ? $data["cityId"] : 0;
            $stateId = isset($data["stateId"]) ? $data["stateId"] : 0;

            return Store::update($storeIdToken, $name, $categoryId, $phone, $streetAddress, $streetNumber, $streetIntNumber, $cityId, $stateId);
        });
    }

    public function updateStorePassword(Request $request, Response $response, array $args)
    {
        return $this->withTryCatch($request, $response, function() use ($request, $args) {

            $data = $request->getParsedBody();
            $storeId = $args["storeId"];

            $storeIdToken = $request->getAttribute('storeIdToken');
            if($storeIdToken != $storeId){
                throw new \Exception("no puedes modificar otro perfil", 400);
            }

            $password = $data["password"];

            if (!v::notEmpty()->stringType()->length(8, 255)->validate($password)) {
                $errors[] = "La contraseña debe tener entre 8 y 255 caracteres.";
            }

            if (!empty($errors)) {
                $errorMessage = implode(", ", $errors);
                throw new \Exception($errorMessage, 400);
            }

            $aesPassword = $this->encryptAES($password);

            Store::updatePassword($storeId, $aesPassword);
        });
    }

    public function updateStoreLogo(Request $request, Response $response, array $args)
    {
        return $this->withTryCatch($request, $response, function() use ($request, $args) {

            $data = $request->getParsedBody();
            $storeId = $args["storeId"];

            $storeIdToken = $request->getAttribute('storeIdToken');
            if($storeIdToken != $storeId){
                throw new \Exception("no puedes modificar otro perfil", 400);
            }

            $logo = $data["logo"];

            if (!v::notEmpty()->stringType()->validate($logo)) {
                $errors[] = "Debes añadir un logo.";
            }

            if (!empty($errors)) {
                $errorMessage = implode(", ", $errors);
                throw new \Exception($errorMessage, 400);
            }

            Store::updateLogo($storeId, $logo);
        });
    }

    public function encryptAES(string $data) {
        $cipher = "aes-256-cbc";
        $options = 0;
        $aesKey = getenv('AES_KEY');
        $iv = getenv('AES_IV');
        return base64_encode(openssl_encrypt($data, $cipher, $aesKey, $options, $iv));
    }

    public function decryptAES(string $data) {
        $cipher = "aes-256-cbc";
        $options = 0;
        $aesKey = getenv('AES_KEY');
        $iv = getenv('AES_IV');
        return openssl_decrypt(base64_decode($data), $cipher, $aesKey, $options, $iv);
    }

    public function generateJwtToken(string $userId, string $userEmail)
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
