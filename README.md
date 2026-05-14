# PHP Template

A small custom PHP MVC-style template with file-based route definitions, simple request handling, JSON API responses, environment-driven configuration, and Eloquent-style models.

## Development Setup

Install PHP dependencies:

```bash
composer install
```

Install Node dependencies:

```bash
yarn install
```

Configure your environment file:

```bash
cp .env.example .env
```

If `.env.example` does not exist yet, create `.env` and set the required app and database values used by `settings/dotenv.php`.

Start dev mode:

```bash
yarn dev
```

`yarn dev` runs the PHP server, Tailwind watcher, and BrowserSync live reload together. The PHP server still runs behind BrowserSync at:

```text
http://localhost:8173
```

BrowserSync proxies the PHP server and reloads when these files change:

```text
routes/**/*.php
resources/**/*.php
app/helpers/**/*.php
app/controllers/**/*.php
public/assets/css/**/*.css
```

Compile Tailwind once:

```bash
yarn tailwind:build
```

Watch PHP route and view files for Tailwind class changes:

```bash
yarn tailwind:watch
```

## cPanel / Apache Hosting

The project includes a root `.htaccess` for cPanel-style Apache hosting.

-   Upload the project so `index.php`, `.htaccess`, `app/`, `routes/`, and `public/` are in the same document root.
-   Static files inside `public/` are served from the site root. For example, `public/assets/css/app.css` loads from `/assets/css/app.css`.
-   Web routes such as `/overview` are rewritten to `index.php`.
-   Private folders such as `app/`, `settings/`, `vendor/`, `resources/`, and `.env` are blocked from direct browser access.

## Project Structure

```text
.
|-- composer.json              # PHP dependencies, autoloading, and scripts
|-- package.json               # Tailwind CSS scripts and Node dependencies
|-- server.php                 # Local PHP server router for public assets
|-- index.php                  # Browser entry point
|-- run.php                    # CLI/testing scratch runner
|-- routes/
|   |-- web.php                # Web routes
|   `-- api.php                # API routes
|-- resources/
|   `-- css/app.css            # Tailwind source CSS
|-- public/
|   `-- assets/css/app.css     # Compiled Tailwind output
|-- app/
|   |-- handlers/              # Router, request, response, route loader
|   |-- controllers/           # Controller classes
|   |-- middlewares/           # Middleware classes
|   |-- models/                # Eloquent models
|   |-- auth/                  # Authentication helpers
|   `-- helpers/               # Shared helpers
|-- configs/                   # Database, logging, and app config classes
|-- settings/                  # Constants, dotenv loading, environment mapping
|-- migrations/                # Database schema/migration helpers
|-- storage/                   # Logs, cache, sessions, uploads
`-- utility/                   # CLI utilities
```

## How The App Boots

The web entry point is `index.php`.

```php
require_once __DIR__ . '/settings/inc.php';
```

`settings/inc.php` loads the full application:

1. Defines base paths from `settings/constants.php`.
2. Loads Composer autoload from `vendor/autoload.php`.
3. Loads `.env` values and maps them to constants in `settings/dotenv.php`.
4. Loads configs, migrations, controllers, helpers, middleware, models, auth classes, and route handlers.
5. Loads `app/handlers/routes-register.php`, which creates the router and dispatches the current request.

## How Routing Works

Routing is handled by `App\Handlers\Router`.

The route registration flow is:

1. `Router::fromGlobals()` creates a `Request` object from `$_SERVER['REQUEST_URI']`, `$_SERVER['REQUEST_METHOD']`, and request headers.
2. A new `Router` instance is created.
3. If the request URI starts with `/api`, the router enables API mode and loads `routes/api.php`.
4. Otherwise, it loads `routes/web.php`.
5. The router matches the current path and HTTP method.
6. If a route matches, the callback is executed.
7. If no route matches, a 404 response is returned.

## Route Files

### Web Routes

Define browser-facing routes in `routes/web.php`.

```php
$router->get('/', function () {
    return 'Welcome to my custom routes';
});
```

Web route responses are sent as `text/html`.

### API Routes

Define API routes in `routes/api.php`.

```php
$router->get('/', function () {
    return 'Welcome to my custom api routes';
});
```

API routes automatically use the `/api` prefix. For example:

```php
$router->get('/menus', function () {
    return Menu::with('children')->whereNull('parent_id')->get()->groupBy('type');
});
```

This route is available at:

```text
GET /api/menus
```

API responses are sent as `application/json`. If a route returns an array or object, the router automatically encodes it as JSON.

## Defining Routes

Use the `$router` variable inside `routes/web.php` or `routes/api.php`.

Supported HTTP methods:

```php
$router->get('/path', $callback);
$router->post('/path', $callback);
$router->put('/path', $callback);
$router->patch('/path', $callback);
$router->delete('/path', $callback);
```

Example:

```php
$router->get('/about', function () {
    return 'About page';
});
```

The web URL is:

```text
GET /about
```

The API URL is:

```text
GET /api/about
```

when the route is defined in `routes/api.php`.

## Dynamic Route Parameters

Dynamic route parameters use `:name` syntax.

```php
use App\Handlers\Request;
use App\Models\User;

$router->get('/user/:id', function (Request $request, $id) {
    return User::findOrFail($id);
});
```

This matches:

```text
GET /api/user/1
```

The router always passes the `Request` object as the first callback argument. Dynamic route values are passed after it, in the same order they appear in the route path.

## Working With Request Data

Use `App\Handlers\Request` in route callbacks when you need query strings, JSON body data, form data, or headers.

```php
use App\Handlers\Request;

$router->post('/login', function (Request $request) {
    return Authenticator::login($request->body);
});
```

Useful request properties and methods:

```php
$request->uri;             // Full request URI
$request->path;            // Path without query string
$request->method;          // HTTP method
$request->queries;         // Parsed query string values
$request->body;            // JSON body or POST form data
$request->headers;         // Request headers
$request->all();           // Body and query values merged together
$request->input('email');  // Get value from body
$request->query('page');   // Get value from query string
$request->header('Auth');  // Get request header
```

For `POST`, `PUT`, `PATCH`, and `DELETE` requests, the router reads JSON from `php://input`. If no JSON body exists, it falls back to `$_POST`.

## Route Responses

The router supports returning strings, arrays, and objects.

Web routes:

```php
$router->get('/hello', function () {
    return '<h1>Hello</h1>';
});
```

API routes:

```php
$router->get('/status', function () {
    return [
        'status' => 'ok',
        'message' => 'API is working',
    ];
});
```

The API response becomes JSON:

```json
{
    "status": "ok",
    "message": "API is working"
}
```

## Tailwind CSS

This project uses Tailwind CSS v4 with the official CLI package.

The source CSS file is:

```text
resources/css/app.css
```

It imports Tailwind and explicitly scans PHP folders where route/view classes are likely to be used:

```css
@import "tailwindcss";

@custom-variant dark (&:where(.dark, .dark *));

@source "../../routes";
@source "../../resources";
@source "../../app/helpers";
@source "../../app/controllers";
```

Dark mode is class-based. Rendered pages use `class="light"` by default on the `<html>` element, so the light theme is the default. Add or switch to a `dark` class on `<html>` to activate `dark:` utilities.

The compiled output file is:

```text
public/assets/css/app.css
```

The web route uses `App\Helpers\Template`, which links the compiled stylesheet with:

```html
<link rel="stylesheet" href="/assets/css/app.css" />
```

When you edit Tailwind classes in `routes/web.php`, keep `yarn tailwind:watch` running so the compiled CSS updates automatically.

## Current API Routes

```text
GET  /api/
GET  /api/menus
GET  /api/roles
GET  /api/user/:id
POST /api/register
POST /api/login
POST /api/logout
GET  /api/test
```

Notes:

-   `/api/register` uses `Authenticator::register($request->all())`.
-   `/api/login` uses `Authenticator::login($request->body)`.
-   `/api/user/:id` loads the user, roles, and menu hierarchy.
-   Routes after `Authenticator::validateToken(Request::getToken())` require a valid bearer token before they can be reached.

## Authentication Token

API token lookup is handled by:

```php
Request::getToken()
```

By default, it reads the `Authorization` header and removes the `Bearer` prefix.

Example header:

```text
Authorization: Bearer your-token-here
```

## Middleware

The router supports middleware through:

```php
$router->addMiddleware(function (Request $request) {
    // Check or modify the request before route matching finishes.
});
```

Middleware callbacks are executed before the matched route callback.

The current middleware classes live in `app/middlewares`, but the router currently accepts callable middleware directly.

## 404 Handling

You can define a custom not-found response:

```php
$router->notFound(function () {
    return 'Custom 404 message';
});
```

For API mode, 404 responses are encoded as JSON and include the path, status, and message.

## Environment Configuration

Environment values are loaded from `.env` and mapped in `settings/dotenv.php`.

Important app variables:

```text
APP_NAME
APP_ENV
APP_DEBUG
APP_URL
APP_TIMEZONE
APP_LOCALE
APP_FALLBACK_LOCALE
```

Development database variables:

```text
DEV_DB_DRIVER
DEV_DB_HOST
DEV_DB_USER
DEV_DB_PASS
DEV_DB_NAME
DEV_DB_PORT
DEV_DB_CHARSET
DEV_DB_COLLATION
DEV_DB_PREFIX
```

Production database variables:

```text
PROD_DB_DRIVER
PROD_DB_HOST
PROD_DB_USER
PROD_DB_PASS
PROD_DB_NAME
PROD_DB_PORT
PROD_DB_CHARSET
PROD_DB_COLLATION
PROD_DB_PREFIX
```

## Adding A New API Route

1. Open `routes/api.php`.
2. Import any classes you need.
3. Add a route with `$router`.

Example:

```php
use App\Handlers\Request;

$router->post('/profile', function (Request $request) {
    return [
        'name' => $request->input('name'),
        'email' => $request->input('email'),
    ];
});
```

This creates:

```text
POST /api/profile
```

Example JSON body:

```json
{
    "name": "John Doe",
    "email": "john@example.com"
}
```

## Adding A New Web Route

Open `routes/web.php` and define the route:

```php
$router->get('/contact', function () {
    return '<h1>Contact Page</h1>';
});
```

This creates:

```text
GET /contact
```

## Important Notes

-   API routes are prefixed automatically with `/api`.
-   Route paths are matched by HTTP method and normalized without trailing slashes.
-   Dynamic parameters use `:param` syntax, not `{param}` syntax.
-   The first route callback argument should be `Request $request` when you need request data or route parameters.
-   API route return values should normally be arrays or objects so the router can send JSON.
-   Web route return values are sent directly as HTML/text.
