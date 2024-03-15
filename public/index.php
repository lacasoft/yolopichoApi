<?php

use Slim\Factory\AppFactory;
use Selective\BasePath\BasePathMiddleware;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Config/db.php';

// Cargar configuraciones desde el archivo .env
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__.'/../');
$dotenv->load();

// Crea una fábrica para Diactoros para decirle a Slim qué PSR-7 usar
$psr17Factory = new Psr17Factory();

// Usa la fábrica PSR-17 para crear la aplicación Slim
AppFactory::setResponseFactory($psr17Factory);

// Crear la aplicación Slim
$app = AppFactory::create();

// Definir Sub Directorio
//$app->setBasePath('/api');

// Agregar middleware
$app->add(new BasePathMiddleware($app));
$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware(true, true);

// Cargar las rutas
require __DIR__ . '/../src/Routes/routes.php';

// Ejecutar la aplicación
$app->run();