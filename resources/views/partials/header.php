<header class="border-b border-gray-200 bg-white dark:border-slate-800 dark:bg-slate-900">
    <nav class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
        <a href="/" class="font-bold text-slate-950 dark:text-white">PHP Template</a>

        <div class="flex gap-4">
            <a href="/" class="<?= ($active ?? '') === 'home' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' ?>">Home</a>
            <a href="/about" class="<?= ($active ?? '') === 'about' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' ?>">About</a>
        </div>
    </nav>
</header>
