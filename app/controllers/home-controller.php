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
                'readmeMarkdown' => $markdown,
                'readmePath' => 'README.md',
                'updatedAt' => is_file($readmePath) ? date('F j, Y g:i A', filemtime($readmePath)) : null,
            ])
            ->render();
    }
}