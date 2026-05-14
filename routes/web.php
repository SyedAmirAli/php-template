<?php
/**
 * Define web routes in this file
 * 
 * Use the $router variable to define routes
 * Example: $router->get('/path', function() { return 'response'; });
 * 
 * @var \App\Handlers\Router $router \App\Handlers\Router instance of web routes
 * @method get(string $path, callable $callback) Handle GET requests
 * @method post(string $path, callable $callback) Handle POST requests  
 * @method put(string $path, callable $callback) Handle PUT requests
 * @method patch(string $path, callable $callback) Handle PATCH requests
 * @method delete(string $path, callable $callback) Handle DELETE requests
 */

use App\Helpers\Template;

$router->get('/', function(){
    $content = <<<HTML
<main class="mx-auto flex min-h-screen max-w-5xl flex-col items-center justify-center px-6 py-16 text-center">
    <span class="rounded-full border border-cyan-400/30 bg-cyan-400/10 px-4 py-1 text-sm font-medium text-cyan-200">
        Custom PHP routes + Tailwind CSS
    </span>

    <h1 class="mt-8 text-4xl font-bold tracking-tight text-blue-500 sm:text-6xl">
        Welcome to your web route
    </h1>

    <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">
        Tailwind scans <code class="rounded bg-slate-800 px-2 py-1 text-cyan-200">routes/web.php</code>
        and recompiles the CSS whenever classes change.
    </p>

    <div class="mt-10 rounded-2xl border border-white/10 bg-white/5 p-6 text-left shadow-2xl shadow-cyan-950/40">
        <p class="text-sm uppercase tracking-wide text-slate-400">Watch command</p>
        <code class="mt-3 block rounded-lg bg-slate-950 px-4 py-3 text-sm text-cyan-200">
            yarn tailwind:watch
        </code>
    </div>
</main>
HTML;

    return Template::page($content, 'Home');
});