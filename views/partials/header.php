<!DOCTYPE html>
<html lang="<?= LANG ?? 'en' ?>" dir="<?= HTML_DIR ?? 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'phpLiteCore') ?></title>

    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
<div class="container py-4">

    <header class="mb-4">
        <nav class="nav nav-pills">
            <a class="nav-link" href="/"><?= htmlspecialchars($navHome) ?></a>
            <a class="nav-link" href="/posts"><?= htmlspecialchars($navPosts) ?></a>
            <a class="nav-link" href="/posts/create"><?= htmlspecialchars($navCreatePost) ?></a> <a class="nav-link" href="/about"><?= htmlspecialchars($navAbout) ?></a>
        </nav>
    </header>

    <main>