<?php

namespace Yolopicho\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

use Yolopicho\Models\DeliveryModel as Delivery;
use Yolopicho\Utilities\ApiResponseFormatter;
use Yolopicho\Traits\ErrorHandlerTrait;

class DeliveryController
{
    use ErrorHandlerTrait;

    public function addDelivery(Request $request, Response $response, array $args): Response
    {
        return $this->withTryCatch($request, $response, function() use ($request, $args) {

            $data = $request->getParsedBody();
            $storeId = $args["storeId"];

            $storeIdToken = $request->getAttribute('storeIdToken');
            if($storeIdToken != $storeId){
                throw new \Exception("no puedes modificar otro perfil", 400);
            }

            if (!isset($data["dishId"], $data["quantity"], $data["amount"], $data["photo"])) {
                throw new \Exception("Se requieren todos los campos");
            }

            $dishId = $data["dishId"];
            $quantity = $data["quantity"];
            $amount = $data["amount"];
            $photo = $data["photo"];

            $errors = [];

            if (!v::number()->validate($dishId)) {
                $errors[] = "El ID de platillo debe ser numérico.";
            }
            if (!v::number()->validate($quantity)) {
                $errors[] = "la cantidad de platillos debe ser numérico.";
            }
            if (!v::notEmpty()->base64()->validate($photo)) {
                $errors[] = "La imagen debe estar en base64";
            }

            if (!empty($errors)) {
                $errorMessage = implode(", ", $errors);
                throw new \Exception($errorMessage,400);
            }

            return Delivery::add($storeId, $amount, $photo, $dishId, $quantity);
        });
    }
}
