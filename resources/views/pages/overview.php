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
                    <span>Markdown is rendered in the browser with CDN scripts.</span>
                </li>
                <li class="flex gap-3">
                    <span class="mt-1 size-2 rounded-full bg-blue-500"></span>
                    <span>Rendered HTML is sanitized before it is inserted into the page.</span>
                </li>
            </ul>
        </aside>

        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-10">
            <div id="readme-preview" class="markdown-preview max-w-none">
                <p class="text-slate-600">Loading README preview...</p>
            </div>
            <script id="readme-markdown" type="text/plain">
                <?= htmlspecialchars($readmeMarkdown ?? '# README not found', ENT_NOQUOTES, 'UTF-8') ?>
            </script>  

        <pre class="bg-slate-100 text-gray-600 p-4 rounded-lg">
# application settings
APP_NAME="User Roles with Menus Management"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8173
APP_TIMEZONE=Asia/Dhaka
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_THEME=default
APP_PORT=8173
APP_KEY=base64:1234567890
APP_ENCRYPTION_KEY=base64:1234567890
APP_COOKIE_NAME=user_roles_with_menus_management
APP_COOKIE_PATH=/
APP_COOKIE_DOMAIN=
APP_COOKIE_SECURE=false
APP_COOKIE_HTTP_ONLY=true
APP_COOKIE_SAMESITE=Lax
APP_COOKIE_EXPIRE=3600 # 60sec * 60 = 1 hour

# database credentials
DEV_DB_DRIVER=mysql
DEV_DB_HOST=localhost
DEV_DB_USER=root
DEV_DB_PASS=
DEV_DB_NAME=user_roles_with_menus_management
DEV_DB_PORT=3306
DEV_DB_CHARSET=utf8
DEV_DB_COLLATION=utf8_unicode_ci
DEV_DB_PREFIX=

# production database credentials
PROD_DB_DRIVER=mysql
PROD_DB_HOST=localhost
PROD_DB_USER=
PROD_DB_PASS=
PROD_DB_NAME=
PROD_DB_PORT=3306
PROD_DB_CHARSET=utf8
PROD_DB_COLLATION=utf8_unicode_ci
PROD_DB_PREFIX=

FAVICON_PATH=https://isara.com/assets/themes/isara/favicon/favicon_isara.png
        </pre>
        </article>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify/dist/purify.min.js"></script>
<script>
    (() => {
        const source = document.getElementById('readme-markdown');
        const target = document.getElementById('readme-preview');

        if (!source || !target || !window.marked || !window.DOMPurify) {
            if (target) {
                target.innerHTML = '<p class="text-red-600">Unable to load Markdown preview scripts.</p>';
            }
            return;
        }

        marked.setOptions({
            breaks: true,
            gfm: true,
        });

        target.innerHTML = DOMPurify.sanitize(marked.parse(source.textContent || ''));
        target.querySelectorAll('a[href^="http"]').forEach((link) => {
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noreferrer');
        });
    })();
</script>
