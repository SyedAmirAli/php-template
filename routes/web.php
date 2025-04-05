<?php
/**
 * Define web routes in this file
 * 
 * Use the $router variable to define routes
 * Example: $router->get('/path', function() { return 'response'; });
 * 
 * @var \App\Handlers\Router $router Router instance
 * @method get(string $path, callable $callback) Handle GET requests
 * @method post(string $path, callable $callback) Handle POST requests  
 * @method put(string $path, callable $callback) Handle PUT requests
 * @method patch(string $path, callable $callback) Handle PATCH requests
 * @method delete(string $path, callable $callback) Handle DELETE requests
 */

$router->get('/', function(){
    return 'Welcome to my custom routes';
});