<?php

namespace App\Handlers;

use App\Configs\Main;
use App\Handlers\Response;

class Request extends Main
{
    public string $uri;
    public string $path;
    public string $method;
    public array $queries;
    public array $body;
    public array $headers;

    public function __construct(string $uri, string $method, array $headers)
    {
        $this->uri = $uri;
        $this->path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $this->method = $method;
        $this->queries = [];
        $this->body = [];
        $this->headers = [];
        $this->headers = $headers;
    }
    
    public static function getRequest()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function all(): array {
        return array_merge($this->body, $this->queries);
    }

    public function input(string $key) {
        if(isset($this->body[$key])) return $this->body[$key];
        return null;
    }

    public function query(string $key) {
        if(isset($this->queries[$key])) return $this->queries[$key];
        return null;
    }

    public function header(string $key) {
        if(isset($this->headers[$key])) return $this->headers[$key];
        return null;
    }

    public function has(string $key) {
        if(isset($this->all[$key])) return true;
        return false;
    }

    public function hasAll(array $keys, bool $strict = true) {
        if($strict) {
            foreach($keys as $key) {
                if(!$this->has($key)) return false;
            }
            return true;
        }
        
        foreach($keys as $key) {
            if(!$this->has($key)) return false;
        }
        return true;
    }

    public static function getToken($tokenType = Response::TOKEN_TYPE_BEARER) {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if(!$token) return null;
        return trim(str_replace($tokenType, '', $token), " ");
    } 
}