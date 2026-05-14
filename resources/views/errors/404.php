<?php
$safePath = htmlspecialchars($path ?? '/', ENT_QUOTES, 'UTF-8');
$safeMessage = htmlspecialchars($message ?? '404 - Page not found', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page not found</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="min-h-screen bg-white text-slate-950 dark:bg-slate-950 dark:text-slate-100">
    <main class="mx-auto flex min-h-screen max-w-3xl flex-col items-center justify-center px-6 text-center">
        <p class="text-sm font-semibold uppercase tracking-wide text-cyan-600 dark:text-cyan-300">404 error</p>
        <h1 class="mt-4 text-4xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-6xl">
            Page not found
        </h1>
        <p class="mt-6 text-lg text-slate-600 dark:text-slate-300">
            <?= $safeMessage ?> for <code class="rounded bg-slate-100 px-2 py-1 text-cyan-700 dark:bg-slate-800 dark:text-cyan-200"><?= $safePath ?></code>.
        </p>
        <a href="/" class="mt-10 rounded-lg bg-cyan-500 px-5 py-3 font-semibold text-white dark:bg-cyan-400 dark:text-slate-950">
            Go back home
        </a>
    </main>
</body>
</html>
