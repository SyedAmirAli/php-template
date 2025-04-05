<?php

namespace App\Handlers;

use App\Handlers\Router;
use App\Configs\Log;

$state = false;

if(CLI_RUNNER) $state = true;
if(!$state) $state = !Router::isExistsRouteServerProperty();

if($state) {
    Log::info('Programme execution in CLI mode and server properties is not exists to run the router.');
    return;
}

$request = Router::fromGlobals();
$router = new Router();

if (strpos($request->uri, '/api') === 0) {
    $router->apiMode(true);
    include_once BASE_DIR . '/routes/api.php';
} else {
    include_once BASE_DIR . '/routes/web.php';
} 

$router->resolve($request);