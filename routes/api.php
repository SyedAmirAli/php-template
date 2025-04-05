<?php

/**
 * Define API routes in this file
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

use App\Auth\Authenticator;
use App\Models\Menu;
use App\Models\Role;
use App\Models\User;
use App\Handlers\Request;
use App\Handlers\Response;

$router->get('/', function(){
    return 'Welcome to my custom api routes';
});

$router->get('/menus', function(){
    return Menu::with('children')->whereNull('parent_id')->get()->groupBy('type');
});

$router->get('/roles', function(){
    return Role::with(['menus', 'permissions'])->get();
});

$router->get('/user/:id', function(Request $request, $id): array {  
    $user = User::findOrFail($id);
    $roles = $user->roles()->with('menus')->get();
    $rawMenus = $roles->pluck('menus')->flatten();
    $roles = $roles->pluck('code')->flatten();
    $menus = Menu::buildMenuHierarchy($rawMenus);
    return compact('user', 'roles', 'menus');
});

$router->post('/register', function(Request $request){
    return Authenticator::register($request->all());
});

$router->post('/login', function(Request $request){
    $login = Authenticator::login($request->body);
    return $login;
});

$router->post('/logout', function(Request $request){
    return Authenticator::logout();
});

Authenticator::validateToken(Request::getToken());

$router->get('/test', function(Request $request){   
    return Authenticator::getCredentials();
});



