<?php

namespace Yolopicho\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

use Yolopicho\Models\DishesModel as Dish;
use Yolopicho\Traits\ErrorHandlerTrait;

class DishesController
{
    use ErrorHandlerTrait;

    public function getDishes(Request $request, Response $response, array $args)
    {
        return $this->withTryCatch($request, $response, function() use ($request, $args) {

            $data = $request->getParsedBody();
            $storeId = $args["storeId"];

            $storeIdToken = $request->getAttribute('storeIdToken');
            if($storeIdToken != $storeId){
                throw new \Exception("no puedes modificar otro perfil", 400);
            }

            return Dish::fetchall($storeId);
        });
    }

    public function addDishes(Request $request, Response $response, array $args)
    {
        return $this->withTryCatch($request, $response, function() use ($request, $args) {

            $data = $request->getParsedBody();
            $storeId = $args["storeId"];

            $storeIdToken = $request->getAttribute('storeIdToken');
            if($storeIdToken != $storeId){
                throw new \Exception("no puedes modificar otro perfil", 400);
            }

            if (!isset($data["name"], $data["cost"])) {
                throw new \Exception("Se requieren todos los campos");
            }

            $name = $data["name"];
            $cost = $data["cost"];

            $errors = [];

            if (!v::notEmpty()->stringType()->length(2, 255)->validate($name)) {
                $errors[] = "El nombre debe tener entre 2 y 255 caracteres.";
            }
            if (!v::notEmpty()->number()->min(1)->validate($cost)) {
                $errors[] = "El costo mínimo es de $1.";
            }

            if (!empty($errors)) {
                $errorMessage = implode(", ", $errors);
                throw new \Exception($errorMessage,400);
            }

            return Dish::add($storeId, $name, $cost);
        });
    }

    public function deleteDish(Request $request, Response $response, array $args)
    {
        return $this->withTryCatch($request, $response, function() use ($request, $args) {

            $data = $request->getParsedBody();
            $storeId = $args["storeId"];

            $storeIdToken = $request->getAttribute('storeIdToken');
            if($storeIdToken != $storeId){
                throw new \Exception("no puedes modificar otro perfil", 400);
            }

            $dishId = $args["dishId"];

            $errors = [];

            if (!v::number()->validate($dishId)) {
                $errors[] = "El ID de ciudad debe ser numérico.";
            }

            if (!empty($errors)) {
                $errorMessage = implode(", ", $errors);
                throw new \Exception($errorMessage,400);
            }

            Dish::deleteDish($storeId, $dishId);
        });
    }
}
