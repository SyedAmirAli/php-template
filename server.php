<?php

$projectDir = __DIR__;
$publicDir = $projectDir . '/public';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$staticPath = rawurldecode($path);
$staticPath = str_starts_with($staticPath, '/public/')
    ? substr($staticPath, strlen('/public'))
    : $staticPath;
$publicFile = realpath($publicDir . $staticPath);

if (
    $path !== '/' &&
    $publicFile !== false &&
    str_starts_with($publicFile, realpath($publicDir) . DIRECTORY_SEPARATOR) &&
    is_file($publicFile)
) {
    $extension = strtolower(pathinfo($publicFile, PATHINFO_EXTENSION));
    $contentTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'svg' => 'image/svg+xml',
        'webp' => 'image/webp',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'txt' => 'text/plain',
        'pdf' => 'application/pdf',
    ];

    header('Content-Type: ' . ($contentTypes[$extension] ?? 'application/octet-stream'));
    readfile($publicFile);
    return true;
}
 
if(!function_exists('view')){
    function view(string $view, array $data = []){
        return App\Helpers\Template::make($view, $data)->render();
    }
}

if(!function_exists('partial')){
    function partial(string $partial, array $data = []){
        return App\Helpers\Template::renderPartial($partial, $data);
    }
}

if(!function_exists('asset')){
    function asset(string $path){
        return '/' . ltrim($path, '/');
    }
}

if(!function_exists('resource_path')){
    function resource_path(string $path){
        return __DIR__ . '/resources/' . ltrim($path, '/');
    }
}

if(!function_exists('public_path')){
    function public_path(string $path){
        return __DIR__ . '/public/' . ltrim($path, '/');
    }
}

if(!function_exists('storage_path')){
    function storage_path(string $path){
        return __DIR__ . '/storage/' . ltrim($path, '/');
    }
}

if(!function_exists('view_path')){
    function view_path(string $path){
        return __DIR__ . '/resources/views/' . ltrim($path, '/');
    }
}

if(!function_exists('components_path')){
    function components_path(string $path){
        return __DIR__ . '/resources/components/' . ltrim($path, '/');
    }
}

if(!function_exists('page_path')){
    function page_path(string $path){
        return __DIR__ . '/resources/pages/' . ltrim($path, '/');
    }
}
 

require __DIR__ . '/index.php';
