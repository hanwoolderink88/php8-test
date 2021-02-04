<?php
declare(strict_types=1);

use TestingTimes\Http\Request\Request;
use TestingTimes\Http\Utils\RequestHelper;
use TestingTimes\Kernel;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$request = new Request(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI'],
    RequestHelper::getRequestHeaders(),
    RequestHelper::getRequestBody(),
);

$kernel = new Kernel();
$kernel->bootstrap();
$response = $kernel->handle($request);

// Return the response
foreach ($response->getHeaders() as $headerName => $headerValues) {
    foreach ($headerValues as $headerValue) {
        header("{$headerName}: {$headerValue}");
    }
}

http_response_code($response->getStatusCode());

echo $response->getBody();