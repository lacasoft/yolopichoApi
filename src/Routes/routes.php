<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Yolopicho\Middlewares\JwtMiddleware;
use Yolopicho\Middlewares\ApiKeyMiddleware;

use Yolopicho\Controllers\CategoryController;
use Yolopicho\Controllers\StateController;
use Yolopicho\Controllers\CityController;
use Yolopicho\Controllers\StoreController;
use Yolopicho\Controllers\DonationController;
use Yolopicho\Controllers\DishesController;
use Yolopicho\Controllers\DeliveryController;


// Ruta raíz para verificar la funcionalidad básica
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write('It works!');
    return $response;
});

// Rutas relacionadas con las categorías de comercios
$app->group('/catalog', function ($app) {
    $app->get('/categories', CategoryController::class . ':getCategories');
    $app->get('/states', StateController::class . ':getStates');
    $app->get('/cities', CityController::class . ':getCities');
    $app->get('/{stateId}/cities', CityController::class . ':getCitiesByState');
})->add(new ApiKeyMiddleware());

// Rutas relacionadas con el acceso
$app->group('/stores', function ($app) {
    $app->post('', StoreController::class . ':addStore');
    $app->post('/login', StoreController::class . ':loginStore');
})->add(new ApiKeyMiddleware());

// Rutas relacionadas con los comercios
$app->group('/stores', function ($app) {
    $app->get('/{storeId}', StoreController::class . ':getStores');
    $app->patch('/{storeId}', StoreController::class . ':updateStore');
    $app->patch('/{storeId}/password', StoreController::class . ':updateStorePassword');
    $app->patch('/{storeId}/logo', StoreController::class . ':updateStoreLogo');
    //$app->get('/{name}', StoreController::class . ':getStoreByName');
    //$app->get('/city/{cityName}', StoreController::class . ':getStoreByCity');
    //$app->get('/category/{categoryName}', StoreController::class . ':getStoreByCategory');
})->add(new JwtMiddleware());

// Rutas relacionadas con las donaciones
$app->group('/donations', function ($app) {
    $app->get('/{storeId}', DonationController::class . ':getDonations');
    $app->post('/{storeId}', DonationController::class . ':addDonations');
    $app->delete('/{storeId}/{donationId}', DonationController::class . ':cancelDonation');
})->add(new JwtMiddleware());

// Rutas relacionadas con los platos (dishes)
$app->group('/dishes', function ($app) {
    $app->get('/{storeId}', DishesController::class . ':getDishes');
    $app->post('/{storeId}', DishesController::class . ':addDishes');
    $app->delete('/{storeId}/{dishId}', DishesController::class . ':deleteDish');
})->add(new JwtMiddleware());

// Rutas relacionadas con el las entregas (delivery)
$app->group('/deliveries', function ($app) {
    $app->post('/{storeId}', DeliveryController::class . ':addDelivery');
})->add(new JwtMiddleware());