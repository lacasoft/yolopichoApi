<?php

namespace Yolopicho\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Yolopicho\Models\CategoryModel as Category;
use Yolopicho\Utilities\ApiResponseFormatter;
use Yolopicho\Traits\ErrorHandlerTrait;

class CategoryController
{
    use ErrorHandlerTrait;

    public function getCategories(Request $request, Response $response): Response
    {
        return $this->withTryCatch($request, $response, function() {
            return Category::fetchAll();
        });
    }
}
