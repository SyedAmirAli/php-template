<?php

namespace App\Handlers;

use App\Configs\Main;
use InvalidArgumentException;
use RuntimeException;
use Throwable;
use App\Handlers\Request;
use App\Configs\Log;
use App\Handlers\BaseRouter;

class Router extends BaseRouter {
    // Initialize routes with valid HTTP methods
    public array $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'PATCH' => [],
        'DELETE' => [],
    ];
    private $notFoundCallback = null; // Fixed typo
    private array $middlewares = [];
    private string $suffix = '';
    private bool $isApi = false;

    public function __construct() {
        // allow CORS
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            Log::error("Preflight request handled");
            exit; // Exit to prevent further execution (if needed
        }
    }

    public function addMiddleware(callable $middleware) {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function addSuffix(string $suffix) {
        $this->suffix = $suffix;
        return $this;
    }

    public function apiMode(bool $enable = true) {
        $this->isApi = $enable;
        $this->addSuffix('/api');

        return $this;
    } 

    // Helper method to add routes with validation
    private function addRoute(string $method, string $path, callable|array $callback)
    {
        $validMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
        if (!in_array($method, $validMethods)) {
            Log::error("Invalid HTTP method: $method");
            throw new InvalidArgumentException("Invalid HTTP method: $method");
        }

        // remove trailing slash
        if($this->isApi && $path === '/') $path = rtrim($path, '/');

        $pathname = "{$this->suffix}{$path}";
        $this->routes[$method][$pathname] = $callback;
        return $this;
    }

    public function get(string $path, callable|array $callback)
    {
        return $this->addRoute('GET', $path, $callback);
    }

    public function post(string $path, callable|array $callback)
    {
        return $this->addRoute('POST', $path, $callback);
    }

    public function put(string $path, callable|array $callback)
    {
        return $this->addRoute('PUT', $path, $callback);
    }

    public function patch(string $path, callable|array $callback)
    {
        return $this->addRoute('PATCH', $path, $callback);
    }

    public function delete(string $path, callable|array $callback)
    {
        return $this->addRoute('DELETE', $path, $callback);
    }

    public function notFound(callable $callback)
    {
        $this->notFoundCallback = $callback;
        return $this;
    }

    public function next() {}

    public function resolve(Request $request): void
    {
        // Normalize path by removing trailing slashes consistently
        $path = rtrim($request->path, '/') ?: '/';
        $method = $request->method;
        
        // Populate request object with additional data
        parse_str(parse_url($request->uri, PHP_URL_QUERY) ?? '', $request->queries);

        // Populate request object with headers
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $jsonBody = file_get_contents('php://input');
            if (!empty($jsonBody)) {
                $decoded = json_decode($jsonBody, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error("Invalid JSON body: ". json_last_error_msg());
                    throw new RuntimeException("Invalid JSON body: " . json_last_error_msg());
                }
                $request->body = $decoded ?? [];
            } elseif (!empty($_POST)) {
                $request->body = $_POST;
            }
        }

        // Execute middleware
        foreach ($this->middlewares as $mw) {
            call_user_func($mw, $request);
        }

        // Match routes (including simple dynamic routes with :param syntax)
        $callback = null;
        $params = [];
        foreach (($this->routes[$method] ?? []) as $routePath => $cb) {
            $pattern = preg_replace('#:[\w]+#', '([^/]+)', $routePath);
            if (preg_match("#^$pattern$#", $path, $matches)) {
                $callback = $cb;
                array_shift($matches); // Remove full match
                $params = $matches;
                break;
            }
        }

        if ($callback) {
            try {
                // Add the request object as the first parameter
                array_unshift($params, $request);
                $result = $this->dispatchRouteCallback($callback, $params);
                $encoded = false;

                if(is_array($result) || is_object($result)){
                    // Format JSON with pretty print and ensure UTF-8 encoding
                    $result = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    $encoded = true;
                }
                
                if($this->isApi){
                    // Set proper JSON content type header
                    header('Content-Type: application/json');
                    $response = $encoded ? $result : json_encode([$result], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                } else {
                    header('Content-Type: text/html');
                    $response = $result;
                }
            } catch (Throwable $exception) {
                $response = $this->isMissingViewException($exception)
                    ? $this->handleNotFound($path)
                    : $this->handleServerError($exception, $path);
            }
        } else {
           $response = $this->handleNotFound($path);
        }

        exit($response);
    }

    private function dispatchRouteCallback(callable|array $callback, array $params): mixed
    {
        if (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        }

        if (
            count($callback) === 2 &&
            is_string($callback[0]) &&
            is_string($callback[1]) &&
            class_exists($callback[0])
        ) {
            $controller = new $callback[0]();
            $method = $callback[1];

            if (!method_exists($controller, $method)) {
                throw new RuntimeException("Controller method not found: {$callback[0]}::{$method}");
            }

            return call_user_func_array([$controller, $method], $params);
        }

        throw new RuntimeException('Invalid route callback.');
    }

    private function handleNotFound(?string $path = null): string
    {
        $encoded = false;
        header("HTTP/1.0 404 Not Found");

        if (is_callable($this->notFoundCallback)) {
            $message = call_user_func($this->notFoundCallback);
            
            if(is_array($message) || is_object($message)){
                $message = json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $encoded = true;
            }
        } else {
            $message = $this->notFoundCallback ?? "404 - Page not found";
        }

        if($this->isApi){
            header('Content-Type: application/json');
            $response = $encoded ? $message : json_encode([
                'path' => $path, 'status' => 'error', 'message' => $message
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } else {
            header('Content-Type: text/html');
            $response = $this->renderWebNotFound($path, $message);
        }

        return $response;
    }

    private function handleServerError(Throwable $exception, ?string $path = null): string
    {
        Log::error($exception->getMessage());
        header("HTTP/1.0 500 Internal Server Error");

        if ($this->isApi) {
            header('Content-Type: application/json');
            return json_encode([
                'path' => $path,
                'status' => 'error',
                'message' => 'Internal server error',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        header('Content-Type: text/html');
        return '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Server Error</title></head><body><h1>500 - Server Error</h1><p>Something went wrong.</p></body></html>';
    }

    private function isMissingViewException(Throwable $exception): bool
    {
        return str_starts_with($exception->getMessage(), 'View file not found:');
    }

    private function renderWebNotFound(?string $path = null, string $message = '404 - Page not found'): string
    {
        $view = BASE_DIR . '/resources/views/errors/404.php';

        if (is_file($view)) {
            ob_start();

            try {
                include $view;
                return ob_get_clean() ?: '';
            } catch (Throwable) {
                ob_end_clean();
            }
        }

        $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        return "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"UTF-8\"><title>404 - Page not found</title></head><body><h1>{$safeMessage}</h1></body></html>";
    }

    // Utility method to create a Request object from the current environment
    public static function fromGlobals(): Request
    {
        $uri = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];
        return new Request($uri, $method, getallheaders());
    }

    public static function isExistsRouteServerProperty(array $properties = ['REQUEST_URI', 'REQUEST_METHOD']): bool
    {
        foreach ($properties as $property) {
            if (!isset($_SERVER[$property]) || empty($_SERVER[$property])) {
                return false;
            }
        }
        return true;
    }

    public function getRouteByPath(string $path): array
    {
        return $this->routes[$path] ?? [];
    } 
}