<?php

namespace App\Helpers {
    use RuntimeException;
    use Throwable;
    use App\Helpers\Resources;

    class Template extends Resources
    {
        private string $page;
        private array $data = [];
        private string $title = '';
        private array $headElements = [];
        private array $footerScripts = [];
        private ?string $header = null;
        private ?string $footer = null;
        private bool $tailwindCss = true;
        private array $bodyClasses = [];
        private array $htmlAttributes = ['lang' => 'en', 'class' => 'light'];
        private array $bodyAttributes = ['class' => 'bg-white text-slate-950 dark:bg-slate-950 dark:text-slate-100'];

        public function __construct(string $page, array $data = [])
        {
            $this->page($page);
            $this->data($data);

            if(env('FAVICON_PATH')) $this->addFavicon(env('FAVICON_PATH'));
        }

        public static function make(string $page, array $data = []): self
        {
            return new self($page, $data);
        }

        public static function asset(string $path): string
        {
            if(str_starts_with($path, 'http')) return $path;
            return '/' . ltrim($path, '/');
        }

        public static function stylesheet(string $path = 'assets/css/app.css'): string
        {
            return '<link rel="stylesheet" href="' . self::e(self::asset($path)) . '" />';
        }

        public static function pageHtml(string $content, string $title = APP_NAME): string
        {
            return self::make('__inline__')
                ->addTitle($title)
                ->addHeadElement(self::stylesheet())
                ->with('__content', $content)
                ->renderInline();
        }

        public static function renderPartial(string $file, array $data = []): string
        {
            return self::renderFile($file, $data);
        }

        public function with(array|string $key, mixed $value = null): self
        {
            return $this->data($key, $value);
        }

        public function data(array|string $key, mixed $value = null): self
        {
            if (is_array($key)) {
                $this->data = array_merge($this->data, $key);
                return $this;
            }

            $this->data[$key] = $value;
            return $this;
        }

        public function page(string $page): self
        {
            $this->page = $page;
            return $this;
        }

        public function addTitle(string $title): self
        {
            $this->title = $title;
            return $this;
        }

        public function title(string $title): self
        {
            return $this->addTitle($title);
        }

        public function addHeadElement(string|array $element): self
        {
            foreach ((array) $element as $item) {
                $this->headElements[] = (string) $item;
            }

            return $this;
        }

        public function addHeadLink(string|array $link, array $attributes = []): self
        {
            foreach ($this->normalizeElementInput($link, 'href', $attributes) as $attrs) {
                $attrs = array_merge(['rel' => 'stylesheet'], $attrs);
                $this->headElements[] = '<link' . self::attrs($attrs) . ' />';
            }

            return $this;
        }

        public function addHeadScript(string|array $script, array $attributes = []): self
        {
            foreach ($this->normalizeElementInput($script, 'src', $attributes) as $attrs) {
                $this->headElements[] = '<script' . self::attrs($attrs) . '></script>';
            }

            return $this;
        }

        public function addFooterScript(string|array $script, array $attributes = []): self
        {
            foreach ($this->normalizeElementInput($script, 'src', $attributes) as $attrs) {
                $this->footerScripts[] = '<script' . self::attrs($attrs) . '></script>';
            }

            return $this;
        }

        public function addHeadMeta(string|array $name, ?string $content = null, array $attributes = []): self
        {
            if (is_string($name)) {
                $this->headElements[] = '<meta' . self::attrs(array_merge([
                    'name' => $name,
                    'content' => $content ?? '',
                ], $attributes)) . ' />';

                return $this;
            }

            if ($this->isList($name)) {
                foreach ($name as $item) {
                    if (is_array($item)) {
                        $this->headElements[] = '<meta' . self::attrs($item) . ' />';
                    }
                }

                return $this;
            }

            if (isset($name['content']) || isset($name['property']) || isset($name['name'])) {
                $this->headElements[] = '<meta' . self::attrs($name) . ' />';
                return $this;
            }

            foreach ($name as $metaName => $metaContent) {
                $this->headElements[] = '<meta' . self::attrs([
                    'name' => (string) $metaName,
                    'content' => (string) $metaContent,
                ]) . ' />';
            }

            return $this;
        }

        public function addMeta(string|array $name, ?string $content = null, array $attributes = []): self
        {
            return $this->addHeadMeta($name, $content, $attributes);
        }

        public function addFavicon(string $path, array $attributes = []): self
        {
            return $this->addHeadLink(array_merge([
                'rel' => 'icon',
                'href' => self::asset($path),
            ], $attributes));
        }

        public function addHeader(string $content, bool $fileInclude = false): self
        {
            $this->header = $fileInclude ? $this->readOptionalView($content) : $content;
            return $this;
        }

        public function addFooter(string $content, bool $fileInclude = false): self
        {
            $this->footer = $fileInclude ? $this->readOptionalView($content) : $content;
            return $this;
        }

        public function addHeaderView(string $file, array $data = []): self
        {
            $this->header = self::renderFile($file, array_merge($this->data, $data));
            return $this;
        }

        public function addFooterView(string $file, array $data = []): self
        {
            $this->footer = self::renderFile($file, array_merge($this->data, $data));
            return $this;
        }

        public function removeHeader(): self
        {
            $this->header = null;
            return $this;
        }

        public function removeFooter(): self
        {
            $this->footer = null;
            return $this;
        }

        public function enableTailwindCss(): self
        {
            $this->tailwindCss = true;
            return $this;
        }

        public function disableTailwindCss(): self
        {
            $this->tailwindCss = false;
            return $this;
        }

        public function disabledTailwindcss(): self
        {
            return $this->disableTailwindCss();
        }

        public function addBodyClass(string|array $class): self
        {
            foreach ((array) $class as $item) {
                foreach (preg_split('/\s+/', trim((string) $item)) ?: [] as $className) {
                    if ($className !== '') {
                        $this->bodyClasses[] = $className;
                    }
                }
            }

            $this->bodyClasses = array_values(array_unique($this->bodyClasses));
            return $this;
        }

        public function bodyClass(string|array $class): self
        {
            return $this->addBodyClass($class);
        }

        public function addHtmlAttribute(string $key, string|bool|null $value = null): self
        {
            $this->htmlAttributes[$key] = $value;
            return $this;
        }

        public function addBodyAttribute(string $key, string|bool|null $value = null): self
        {
            $this->bodyAttributes[$key] = $value;
            return $this;
        }

        public function render(): string
        {
            $content = $this->page === '__inline__'
                ? (string) ($this->data['__content'] ?? '')
                : $this->renderContent();

            $head = $this->renderHead();
            $body = trim(implode("\n", array_filter([
                $this->header,
                $content,
                $this->footer,
                implode("\n", $this->footerScripts),
            ], static fn ($item) => $item !== null && $item !== '')));

            $htmlAttributes = self::attrs($this->htmlAttributes);
            $bodyAttributes = $this->bodyAttributes;

            if (!empty($this->bodyClasses)) {
                $bodyAttributes['class'] = trim(($bodyAttributes['class'] ?? '') . ' ' . implode(' ', $this->bodyClasses));
            }

            $bodyAttributes = self::attrs($bodyAttributes);

            return <<<HTML
<!DOCTYPE html>
<html{$htmlAttributes}>
<head>
{$head}
</head>
<body{$bodyAttributes}>
{$body}
</body>
</html>
HTML;
        }

        public function renderContent(): string
        {
            return self::renderFile($this->page, $this->data);
        }

        public function __toString(): string
        {
            try {
                return $this->render();
            } catch (Throwable) {
                return '';
            }
        }

        private function renderInline(): string
        {
            return $this->render();
        }

        private function renderHead(): string
        {
            $title = $this->title !== '' ? $this->title : (defined('APP_NAME') ? APP_NAME : '');
            $elements = [
                '<meta charset="UTF-8" />',
                '<meta name="viewport" content="width=device-width, initial-scale=1.0" />',
                '<title>' . self::e($title) . '</title>',
            ];

            if ($this->tailwindCss) {
                $elements[] = self::stylesheet();
            }

            return '    ' . implode("\n    ", array_merge($elements, $this->headElements));
        }

        private function readOptionalView(string $content): string
        {
            $file = self::resolveViewPath($content);

            if (!is_file($file)) {
                return $content;
            }

            return file_get_contents($file) ?: '';
        }

        private function normalizeElementInput(string|array $input, string $primaryKey, array $attributes = []): array
        {
            if (is_string($input)) {
                return [array_merge([$primaryKey => $input], $attributes)];
            }

            if ($this->isList($input)) {
                return array_values(array_filter($input, 'is_array'));
            }

            return [$input];
        }

        private function isList(array $array): bool
        {
            return array_is_list($array);
        }

        private static function basePath(): string
        {
            return BASE_DIR . '/resources/views';
        }

        private static function normalizeViewName(string $file): string
        {
            if (
                str_contains($file, "\0") ||
                str_starts_with($file, '/') ||
                preg_match('/^[A-Za-z]:[\\\\\/]/', $file)
            ) {
                throw new RuntimeException("Invalid view path: {$file}");
            }

            $name = trim($file);
            $name = preg_replace('/\.php$/', '', $name) ?? $name;
            $name = str_replace(['\\', '.'], '/', $name);
            $name = trim($name, '/');

            if ($name === '') {
                throw new RuntimeException('View path cannot be empty.');
            }

            foreach (explode('/', $name) as $part) {
                if ($part === '..' || $part === '') {
                    throw new RuntimeException("Invalid view path: {$file}");
                }
            }

            return $name . '.php';
        }

        private static function resolveViewPath(string $file): string
        {
            $base = realpath(self::basePath());

            if ($base === false) {
                throw new RuntimeException('View base path does not exist: ' . self::basePath());
            }

            $path = $base . '/' . self::normalizeViewName($file);
            $real = realpath($path);

            if ($real !== false) {
                if (!str_starts_with($real, $base . DIRECTORY_SEPARATOR) && $real !== $base) {
                    throw new RuntimeException("View path is outside resources/views: {$file}");
                }

                return $real;
            }

            $directory = realpath(dirname($path));

            if ($directory === false || !str_starts_with($directory, $base)) {
                throw new RuntimeException("View path is outside resources/views: {$file}");
            }

            return $path;
        }

        private static function renderFile(string $file, array $data = []): string
        {
            $path = self::resolveViewPath($file);

            if (!is_file($path)) {
                throw new RuntimeException("View file not found: {$file}");
            }

            ob_start();

            try {
                extract($data, EXTR_SKIP);
                include $path;
                return ob_get_clean() ?: '';
            } catch (Throwable $exception) {
                ob_end_clean();
                throw $exception;
            }
        }

        private static function e(mixed $value): string
        {
            return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        }

        private static function attrs(array $attributes): string
        {
            $html = [];

            foreach ($attributes as $key => $value) {
                if ($value === false || $value === null) {
                    continue;
                }

                $escapedKey = self::e($key);

                if ($value === true) {
                    $html[] = $escapedKey;
                    continue;
                }

                $html[] = $escapedKey . '="' . self::e($value) . '"';
            }

            return empty($html) ? '' : ' ' . implode(' ', $html);
        }
    }
}

namespace {
    use App\Helpers\Template;

    if (!function_exists('template')) {
        function template(string $page, array $data = []): Template
        {
            return new Template($page, $data);
        }
    }

    if (!function_exists('pageTemplate')) {
        function page(string $page, array $data = []): Template
        {
            return new Template("pages/{$page}", $data);
        }
    }

    if (!function_exists('view')) {
        function view(string $page, array $data = []): string
        {
            return Template::make($page, $data)->renderContent();
        }
    }

    if (!function_exists('partial')) {
        function partial(string $file, array $data = []): string
        {
            return Template::renderPartial($file, $data);
        }
    }
}
