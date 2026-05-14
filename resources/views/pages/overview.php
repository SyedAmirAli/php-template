<main class="min-h-screen bg-slate-50 text-slate-950">
    <header class="border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 px-6 py-6 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-blue-600">Project overview</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight">README preview</h1>
                <p class="mt-2 text-slate-600">
                    Rendering <code class="rounded bg-slate-100 px-2 py-1 text-sm font-semibold text-rose-700"><?= htmlspecialchars($readmePath ?? 'README.md', ENT_QUOTES, 'UTF-8') ?></code>
                    <?php if (!empty($updatedAt)): ?>
                        <span class="text-slate-400">Last updated <?= htmlspecialchars($updatedAt, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </p>
            </div>

            <a href="/" class="inline-flex items-center justify-center rounded-lg bg-slate-950 px-5 py-3 font-semibold text-white transition hover:bg-slate-800">
                Back to home
            </a>
        </div>
    </header>

    <section class="mx-auto grid max-w-7xl gap-8 px-6 py-10 lg:grid-cols-[280px_1fr]">
        <aside class="h-max rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Preview Notes</h2>
            <ul class="mt-5 space-y-3 text-sm text-slate-600">
                <li class="flex gap-3">
                    <span class="mt-1 size-2 rounded-full bg-blue-500"></span>
                    <span>Content is loaded directly from the root README file.</span>
                </li>
                <li class="flex gap-3">
                    <span class="mt-1 size-2 rounded-full bg-blue-500"></span>
                    <span>Generated HTML is escaped before Markdown formatting is applied.</span>
                </li>
                <li class="flex gap-3">
                    <span class="mt-1 size-2 rounded-full bg-blue-500"></span>
                    <span>Code blocks, headings, lists, links, and inline code are supported.</span>
                </li>
            </ul>
        </aside>

        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-10">
            <div class="max-w-none">
                <?= $readmeHtml ?? '<p class="text-slate-600">README content is not available.</p>' ?>
            </div>
        </article>
    </section>
</main>
