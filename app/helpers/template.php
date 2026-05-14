<?php

namespace App\Helpers;

class Template extends Resources
{
    public static function asset(string $path): string
    {
        return '/' . ltrim($path, '/');
    }

    public static function stylesheet(string $path = 'public/assets/css/app.css'): string
    {
        $href = htmlspecialchars(self::asset($path), ENT_QUOTES, 'UTF-8');

        return "<link rel=\"stylesheet\" href=\"{$href}\">";
    }

    public static function page(string $content, string $title = APP_NAME): string
    {
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $stylesheet = self::stylesheet();

        return <<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$safeTitle}</title>
    {$stylesheet}
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    {$content}
</body>
</html>
HTML;
    }
}
