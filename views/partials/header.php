<!DOCTYPE html>
<html lang="<?= LANG ?? 'en' ?>" dir="<?= HTML_DIR ?? 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'phpLiteCore') ?></title>
    <link href="//cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 2rem; padding-bottom: 2rem; }
        .footer { margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #eee; color: #777; }
        .post-item { margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #eee; }
        .post-item h2 a { text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <header class="mb-4">
        <nav class="nav">
            <a class="nav-link" href="/">Home</a>
            <a class="nav-link" href="/posts">Posts</a>
            <a class="nav-link" href="/about">About</a>
        </nav>
    </header>
    <main>