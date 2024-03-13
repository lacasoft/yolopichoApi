<?php

namespace Yolopicho\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

use Yolopicho\Models\DonationModel as Donation;
use Yolopicho\Traits\ErrorHandlerTrait;

class DonationController
{
    use ErrorHandlerTrait;

    public function getDonations(Request $request, Response $response, array $args)
    {
        return $this->withTryCatch($request, $response, function() use ($request, $args) {

            $data = $request->getParsedBody();
            $storeId = $args["storeId"];

            $storeIdToken = $request->getAttribute('storeIdToken');
            if($storeIdToken != $storeId){
                throw new \Exception("no puedes modificar otro perfil", 400);
            }

            $queryFilters = $request->getQueryParams();

            return Donation::fetchall($storeId, $queryFilters);
        });
    }

    public function addDonations(Request $request, Response $response, array $args)
    {
        return $this->withTryCatch($request, $response, function() use ($request, $args) {

            $data = $request->getParsedBody();
            $storeId = $args["storeId"];

            $storeIdToken = $request->getAttribute('storeIdToken');
            if($storeIdToken != $storeId){
                throw new \Exception("no puedes modificar otro perfil", 400);
            }

            if (!isset($data["email"], $data["amount"])) {
                throw new \Exception("Se requieren todos los campos");
            }

            $email = $data["email"];
            $amount = $data["amount"];

            $errors = [];

            if (!v::notEmpty()->email()->validate($email)) {
                $errors[] = "El correo electrónico es inválido.";
            }
            if (!v::notEmpty()->number()->min(1)->validate($amount)) {
                $errors[] = "La cantidad mínima de donación es $1.";
            }

            if (!empty($errors)) {
                $errorMessage = implode(", ", $errors);
                throw new \Exception($errorMessage,400);
            }

            Donation::add($email, $storeId, $amount);
        });
    }

    public function cancelDonation(Request $request, Response $response , array $args)
    {
        return $this->withTryCatch($request, $response, function() use ($request, $args) {

            $data = $request->getParsedBody();
            $storeId = $args["storeId"];

            $storeIdToken = $request->getAttribute('storeIdToken');
            if($storeIdToken != $storeId){
                throw new \Exception("no puedes modificar otro perfil", 400);
            }

            $donationId = $args["donationId"];
            $note = $data["note"];

            $errors = [];

            if (!v::notEmpty()->stringType()->validate($donationId)) {
                $errors[] = "El Id de la donación es obligatorio";
            }
            if (!v::notEmpty()->stringType()->length(10, null)->validate($note)) {
                $errors[] = "La nota debe tener minimo 10 caracteres.";
            }

            if (!empty($errors)) {
                $errorMessage = implode(", ", $errors);
                throw new \Exception($errorMessage,400);
            }

            Donation::cancelDonation($donationId, $note);
        });
    }
}
