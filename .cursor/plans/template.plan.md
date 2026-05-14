````txt
You are working inside my existing custom PHP framework repository:
SyedAmirAli/php-template

Goal:
Create a lightweight, chainable PHP frontend template management system inside:

app/helpers/template.php

This should be class-based, simple, and practical. Inspired by Laravel Blade/template rendering, but DO NOT build a Blade parser. This project uses normal PHP files.

Important project rules:
- Use normal PHP template files.
- Base frontend view folder should be: resources/views
- Add the helper file at: app/helpers/template.php
- Load it from settings/inc.php near the other helper files:
  require_once BASE_DIR . '/app/helpers/template.php';
- Do not break existing router/controller structure.
- Do not introduce Composer packages.
- Do not use eval.
- Use output buffering where PHP template execution is needed.
- Use file_get_contents where raw file content is requested.
- Compatible with PHP 8+.
- Prevent directory traversal.
- All template files must resolve inside resources/views.
- Keep code clean, readable, and framework-free.

Create a `Template` class with chainable methods.

Main usage example:

```php
return Template::make('pages/home')
    ->addTitle('Home Page')
    ->addHeadElement('<link rel="preconnect" href="https://fonts.googleapis.com">')
    ->addHeadLink('./assets/css/app.css')
    ->addHeadScript('./assets/js/app.js', ['defer' => true])
    ->addHeadMeta('description', 'Welcome to my custom PHP template system.')
    ->addFavicon('./favicon.ico')
    ->addHeader('partials/header.php', true)
    ->addFooter('partials/footer.php', true)
    ->with([
        'heading' => 'Welcome',
        'message' => 'This page is rendered using Template class.'
    ])
    ->enableTailwindCss()
    ->render();
````

Also support raw header/footer content:

```php
return template('pages/home')
    ->addTitle('Home')
    ->addHeader('<header>My Header</header>')
    ->addFooter('<footer>My Footer</footer>')
    ->render();
```

Important header/footer behavior:
Functions like `addHeader()` and `addFooter()` should support two modes:

1. Raw content mode:

```php
->addHeader('<header>Hello</header>')
->addFooter('<footer>Copyright</footer>')
```

2. File include/content mode:

```php
->addHeader('partials/header.php', true)
->addFooter('partials/footer.php', true)
```

The method logic should be similar to this idea:

```php
function addHeader(string $content, bool $fileInclude = false): self
{
    if (!$fileInclude) {
        $this->header = $content;
        return $this;
    }

    $file = self::resolveViewPath($content);

    if (!file_exists($file)) {
        $this->header = $content;
        return $this;
    }

    $this->header = file_get_contents($file);
    return $this;
}
```

But implement it cleanly inside the `Template` class.

Important:

-   `addHeader()` and `addFooter()` should return `$this`, not string, because the Template class must support method chaining.
-   If `$fileInclude = false`, store the given string as raw content.
-   If `$fileInclude = true`, resolve the file from `resources/views`.
-   If the file exists, read content using `file_get_contents()`.
-   If the file does not exist, keep the original string as fallback content.
-   Do not throw error for missing header/footer file. Just use given string as fallback.
-   Header/footer are simple HTML/PHP file-content blocks.
-   For dynamic PHP execution, also add separate methods named `addHeaderView()` and `addFooterView()`.

Required class API:

```php
class Template
{
    public function __construct(string $page, array $data = []);

    public static function make(string $page, array $data = []): self;

    public function with(array|string $key, mixed $value = null): self;
    public function data(array|string $key, mixed $value = null): self;

    public function page(string $page): self;

    public function addTitle(string $title): self;
    public function title(string $title): self;

    public function addHeadElement(string|array $element): self;

    public function addHeadLink(string|array $link, array $attributes = []): self;

    public function addHeadScript(string|array $script, array $attributes = []): self;
    public function addFooterScript(string|array $script, array $attributes = []): self;

    public function addHeadMeta(string|array $name, ?string $content = null, array $attributes = []): self;
    public function addMeta(string|array $name, ?string $content = null, array $attributes = []): self;

    public function addFavicon(string $path, array $attributes = []): self;

    public function addHeader(string $content, bool $fileInclude = false): self;
    public function addFooter(string $content, bool $fileInclude = false): self;

    public function addHeaderView(string $file, array $data = []): self;
    public function addFooterView(string $file, array $data = []): self;

    public function removeHeader(): self;
    public function removeFooter(): self;

    public function enableTailwindCss(): self;
    public function disableTailwindCss(): self;
    public function disabledTailwindcss(): self;

    public function addBodyClass(string|array $class): self;
    public function bodyClass(string|array $class): self;

    public function addHtmlAttribute(string $key, string|bool|null $value = null): self;
    public function addBodyAttribute(string $key, string|bool|null $value = null): self;

    public function render(): string;
    public function renderContent(): string;
    public function __toString(): string;
}
```

Constructor behavior:

```php
public function __construct(string $page, array $data = [])
```

The `$page` can be:

-   `pages/home`
-   `pages/home.php`
-   `pages.home`

All should resolve to:

```txt
resources/views/pages/home.php
```

Data methods:

```php
->with('name', 'Amir')
->with([
    'heading' => 'Hello',
    'message' => 'World'
])
```

Title methods:

```php
->addTitle('Home')
->title('Home')
```

`title()` should be alias of `addTitle()`.

Head element method:

```php
->addHeadElement('<link rel="stylesheet" href="./styles.css">')

->addHeadElement([
    '<link rel="stylesheet" href="./styles.css">',
    '<link rel="icon" href="./favicon.ico">'
])
```

Rules:

-   Accept raw HTML string or array of raw HTML strings.
-   Do not escape raw head element string.
-   Document this method as trusted raw HTML only.

Head link method:

```php
->addHeadLink('./styles.css')

->addHeadLink('./styles.css', [
    'rel' => 'stylesheet',
    'media' => 'all'
])

->addHeadLink([
    'href' => './styles.css',
    'rel' => 'stylesheet'
])

->addHeadLink([
    ['href' => './reset.css', 'rel' => 'stylesheet'],
    ['href' => './theme.css', 'rel' => 'stylesheet', 'media' => 'all']
])
```

Rules:

-   If string is passed, treat it as href.
-   Default rel should be stylesheet.
-   Render:

```html
<link rel="stylesheet" href="./styles.css" />
```

-   Escape generated attributes.

Head script method:

```php
->addHeadScript('./app.js')

->addHeadScript('./app.js', [
    'defer' => true
])

->addHeadScript([
    'src' => './app.js',
    'type' => 'module'
])

->addHeadScript([
    ['src' => './a.js', 'defer' => true],
    ['src' => './b.js', 'type' => 'module']
])
```

Rules:

-   Render:

```html
<script src="./app.js"></script>
```

-   Boolean attributes like defer, async, nomodule should render correctly.
-   false/null attributes should be skipped.

Footer script method:
Same as addHeadScript(), but output before closing body.

Meta method:

```php
->addHeadMeta('description', 'Page description')

->addHeadMeta('viewport', 'width=device-width, initial-scale=1')

->addHeadMeta([
    'description' => 'Home page',
    'author' => 'Amir'
])

->addHeadMeta([
    'property' => 'og:title',
    'content' => 'Home Page'
])

->addHeadMeta([
    ['name' => 'description', 'content' => 'Home page'],
    ['property' => 'og:title', 'content' => 'Home']
])
```

`addMeta()` should be alias of `addHeadMeta()`.

Favicon method:

```php
->addFavicon('./favicon.ico')
```

Default render:

```html
<link rel="icon" href="./favicon.ico" />
```

Header/footer methods:

```php
->addHeader('<header>Raw Header</header>')
->addFooter('<footer>Raw Footer</footer>')
```

This should store raw string directly.

```php
->addHeader('partials/header.php', true)
->addFooter('partials/footer.php', true)
```

This should load file content using `file_get_contents()` from:

```txt
resources/views/partials/header.php
resources/views/partials/footer.php
```

If file is missing, use the original string as fallback content.

Also add dynamic PHP rendered view methods:

```php
->addHeaderView('partials/header', ['active' => 'home'])
->addFooterView('partials/footer')
```

These should execute PHP template files using output buffering and extracted data.

Tailwind methods:

```php
->enableTailwindCss()
->disableTailwindCss()
->disabledTailwindcss()
```

Rules:

-   `disabledTailwindcss()` should be alias of `disableTailwindCss()` because I may accidentally call it that way.
-   If Tailwind is enabled, output this in the head:

```html
<script src="https://cdn.tailwindcss.com"></script>
```

Body and HTML helpers:

```php
->addBodyClass('bg-gray-50')

->addBodyClass([
    'min-h-screen',
    'text-gray-900'
])

->addHtmlAttribute('lang', 'en')
->addBodyAttribute('data-theme', 'light')
```

Render behavior:

`render()` should return full HTML:

```html
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>...</title>
        ...
    </head>
    <body>
        ... header ... ... page content ... ... footer ... ... footer scripts ...
    </body>
</html>
```

`renderContent()` should only render the main page file without full HTML shell.

`__toString()` should return `render()` safely.

Path resolver requirements:

Create internal/private helper methods:

```php
private static function basePath(): string;
private static function normalizeViewName(string $file): string;
private static function resolveViewPath(string $file): string;
private static function renderFile(string $file, array $data = []): string;
private static function e(mixed $value): string;
private static function attrs(array $attributes): string;
```

Path rules:

-   Base path: `BASE_DIR . '/resources/views'`
-   Support dot notation:
    `pages.home` => `pages/home.php`
-   Support direct slash:
    `pages/home.php`
-   Add `.php` if missing.
-   Reject:

    -   `..`
    -   null byte
    -   absolute paths
    -   paths outside `resources/views`

For main page rendering:

-   If the main page file is missing, throw `RuntimeException`.

For header/footer fileInclude mode:

-   If file is missing, do not throw.
-   Use original content string as fallback.

Escaping rules:

-   Escape all generated attributes using `htmlspecialchars()`.
-   Boolean true attributes render as only key:
    `defer`
-   Boolean false/null attributes are skipped.
-   Raw strings passed through `addHeadElement()`, `addHeader()`, and `addFooter()` are trusted raw HTML and should not be escaped.

Global helper functions:
Add these in the same `template.php`, guarded with `if (!function_exists(...))`:

```php
function template(string $page, array $data = []): Template
{
    return new Template($page, $data);
}

function view(string $page, array $data = []): string
{
    return Template::make($page, $data)->renderContent();
}

function partial(string $file, array $data = []): string
{
    return Template::renderPartial($file, $data);
}
```

For `partial()`, add a public static method:

```php
public static function renderPartial(string $file, array $data = []): string
```

Example inside any PHP view:

```php
<?= partial('partials/nav', ['active' => 'home']) ?>
```

Create example files if missing:

```txt
resources/views/pages/home.php
resources/views/partials/header.php
resources/views/partials/footer.php
```

Example `resources/views/pages/home.php`:

```php
<section class="max-w-5xl mx-auto px-6 py-16">
    <h1 class="text-4xl font-bold">
        <?= htmlspecialchars($heading ?? 'Welcome', ENT_QUOTES, 'UTF-8') ?>
    </h1>

    <p class="mt-4 text-lg text-gray-600">
        <?= htmlspecialchars($message ?? 'Rendered from resources/views.', ENT_QUOTES, 'UTF-8') ?>
    </p>
</section>
```

Example `resources/views/partials/header.php`:

```php
<header class="border-b bg-white">
    <nav class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
        <a href="/" class="font-bold">PHP Template</a>

        <div class="flex gap-4">
            <a href="/">Home</a>
            <a href="/about">About</a>
        </div>
    </nav>
</header>
```

Example `resources/views/partials/footer.php`:

```php
<footer class="border-t bg-white">
    <div class="max-w-5xl mx-auto px-6 py-6 text-sm text-gray-500">
        &copy; <?= date('Y') ?> PHP Template
    </div>
</footer>
```

Update `routes/web.php` homepage example:

```php
$router->get('/', function () {
    return template('pages/home')
        ->addTitle('Home')
        ->addHeadMeta('description', 'Welcome to my custom PHP template system.')
        ->addHeader('partials/header.php', true)
        ->addFooter('partials/footer.php', true)
        ->enableTailwindCss()
        ->with([
            'heading' => 'Welcome to my custom PHP template routes',
            'message' => 'This page is rendered from resources/views using the Template class.'
        ])
        ->render();
});
```

Also add another example route using raw header/footer content:

```php
$router->get('/raw-template', function () {
    return template('pages/home')
        ->addTitle('Raw Template Example')
        ->addHeader('<header style="padding:20px;border-bottom:1px solid #ddd;">Raw Header</header>')
        ->addFooter('<footer style="padding:20px;border-top:1px solid #ddd;">Raw Footer</footer>')
        ->with([
            'heading' => 'Raw Header/Footer Example',
            'message' => 'Header and footer are passed as raw strings.'
        ])
        ->render();
});
```

Also add another example route using dynamic PHP header/footer view rendering:

```php
$router->get('/view-template', function () {
    return template('pages/home')
        ->addTitle('View Template Example')
        ->addHeaderView('partials/header', ['active' => 'home'])
        ->addFooterView('partials/footer')
        ->enableTailwindCss()
        ->with([
            'heading' => 'Dynamic Header/Footer View Example',
            'message' => 'Header and footer are executed as PHP partial views.'
        ])
        ->render();
});
```

Implementation quality:

-   Keep all code in `app/helpers/template.php`.
-   Use private properties.
-   Use method chaining by returning `$this`.
-   Do not namespace unless current project style requires it.
-   Do not use `include` or `extends` as function names because they are PHP reserved keywords.
-   Do not build a Blade parser.
-   Do not use eval.
-   Keep generated HTML clean.
-   Throw clear `RuntimeException` for missing main page template.
-   Do not throw for missing header/footer when using `addHeader($content, true)` or `addFooter($content, true)`.
-   Ensure final code has valid PHP syntax.
-   Ensure homepage route works after implementation.
