<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class HomeController extends BaseController
{
    public function index()
    {
        return page('home')
            ->addTitle('Schedule Your Technical Session')
            ->addFavicon('https://isara.com/assets/themes/isara/favicon/favicon_isara.png')
            ->addHeadMeta('description', 'Schedule a 30-minute technical session with ISARA.')
            ->addBodyClass('bg-black text-white')
            ->render();
    }

    public function overview()
    {
        $readmePath = BASE_DIR . '/README.md';
        $markdown = is_file($readmePath) ? file_get_contents($readmePath) : '# README not found';

        return page('overview')
            ->addTitle('Project Overview')
            ->addHeadMeta('description', 'Preview the README documentation for this PHP template project.')
            ->addBodyClass('bg-slate-50 text-slate-950')
            ->with([
                'readmeHtml' => self::renderMarkdown($markdown ?: ''),
                'readmePath' => 'README.md',
                'updatedAt' => is_file($readmePath) ? date('F j, Y g:i A', filemtime($readmePath)) : null,
            ])
            ->render();
    }

    private static function renderMarkdown(string $markdown): string
    {
        $lines = preg_split('/\R/', $markdown) ?: [];
        $html = [];
        $inCodeBlock = false;
        $codeLanguage = '';
        $codeLines = [];
        $listType = null;

        $closeList = static function () use (&$html, &$listType): void {
            if ($listType !== null) {
                $html[] = "</{$listType}>";
                $listType = null;
            }
        };

        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '```')) {
                if ($inCodeBlock) {
                    $html[] = '<pre class="my-5 overflow-x-auto rounded-xl bg-slate-950 p-5 text-sm text-slate-100 shadow-inner"><code data-language="' . self::e($codeLanguage) . '">' . self::e(implode("\n", $codeLines)) . '</code></pre>';
                    $inCodeBlock = false;
                    $codeLanguage = '';
                    $codeLines = [];
                    continue;
                }

                $closeList();
                $inCodeBlock = true;
                $codeLanguage = trim(substr(trim($line), 3));
                continue;
            }

            if ($inCodeBlock) {
                $codeLines[] = $line;
                continue;
            }

            $trimmed = trim($line);

            if ($trimmed === '') {
                $closeList();
                continue;
            }

            if (preg_match('/^(#{1,6})\s+(.+)$/', $trimmed, $matches)) {
                $closeList();
                $level = min(strlen($matches[1]), 3);
                $classes = [
                    1 => 'mt-0 mb-6 text-4xl font-black tracking-tight text-slate-950',
                    2 => 'mt-10 mb-4 border-b border-slate-200 pb-3 text-2xl font-bold text-slate-900',
                    3 => 'mt-8 mb-3 text-xl font-bold text-slate-900',
                ];
                $html[] = '<h' . $level . ' class="' . $classes[$level] . '">' . self::inlineMarkdown($matches[2]) . '</h' . $level . '>';
                continue;
            }

            if (preg_match('/^-\s+(.+)$/', $trimmed, $matches)) {
                if ($listType !== 'ul') {
                    $closeList();
                    $listType = 'ul';
                    $html[] = '<ul class="my-4 list-disc space-y-2 pl-6 text-slate-700">';
                }

                $html[] = '<li>' . self::inlineMarkdown($matches[1]) . '</li>';
                continue;
            }

            if (preg_match('/^\d+\.\s+(.+)$/', $trimmed, $matches)) {
                if ($listType !== 'ol') {
                    $closeList();
                    $listType = 'ol';
                    $html[] = '<ol class="my-4 list-decimal space-y-2 pl-6 text-slate-700">';
                }

                $html[] = '<li>' . self::inlineMarkdown($matches[1]) . '</li>';
                continue;
            }

            $closeList();
            $html[] = '<p class="my-4 leading-7 text-slate-700">' . self::inlineMarkdown($trimmed) . '</p>';
        }

        if ($inCodeBlock) {
            $html[] = '<pre class="my-5 overflow-x-auto rounded-xl bg-slate-950 p-5 text-sm text-slate-100 shadow-inner"><code>' . self::e(implode("\n", $codeLines)) . '</code></pre>';
        }

        $closeList();
        return implode("\n", $html);
    }

    private static function inlineMarkdown(string $text): string
    {
        $escaped = self::e($text);

        $escaped = preg_replace('/`([^`]+)`/', '<code class="rounded bg-slate-100 px-1.5 py-0.5 text-sm font-semibold text-rose-700">$1</code>', $escaped) ?? $escaped;
        $escaped = preg_replace('/\*\*([^*]+)\*\*/', '<strong class="font-bold text-slate-950">$1</strong>', $escaped) ?? $escaped;
        $escaped = preg_replace('/\[([^\]]+)\]\((https?:\/\/[^)]+)\)/', '<a class="font-semibold text-blue-600 underline underline-offset-4" href="$2" target="_blank" rel="noreferrer">$1</a>', $escaped) ?? $escaped;

        return $escaped;
    }

    private static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}