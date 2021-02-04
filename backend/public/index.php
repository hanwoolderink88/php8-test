<?php
declare(strict_types=1);

use Nyholm\Psr7\ServerRequest;
use TestingTimes\App\Controllers\IndexController;
use TestingTimes\App\Controllers\OrderController;
use TestingTimes\App\Controllers\PostController;
use TestingTimes\App\Controllers\ProductController;
use TestingTimes\App\Controllers\UserController;
use TestingTimes\Http\Utils\RequestHelper;
use TestingTimes\Routing\RouteMatcher;
use TestingTimes\Routing\RouteParser;
use TestingTimes\Routing\Router;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Create router and add routes.. this can be cached..
$router = new Router();
$router->flushRoutes();
$routeParser = new RouteParser($router);
$routeParser
    ->byClassMethods(IndexController::class)
    ->byClassMethods(OrderController::class)
    ->byResource(UserController::class)
    ->byClassMethods(PostController::class)
    ->byClassMethods(ProductController::class);

$content = file_get_contents('php://input');

// Create a psr request object
$request = new ServerRequest(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI'],
    RequestHelper::getRequestHeaders(),
    $content,
);

// Match the request with a route in the router and return the response body of the controller method
$routeMatcher = new RouteMatcher($router);
$response = $routeMatcher->handle($request);

// Return the response
foreach ($response->getHeaders() as $headerName => $headerValues) {
    foreach ($headerValues as $headerValue){
        header("{$headerName}: {$headerValue}");
    }
}
http_response_code($response->getStatusCode());

echo $response->getBody();
