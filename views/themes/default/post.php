<!DOCTYPE html>
<html lang="<?= LANG ?? 'en' ?>" dir="<?= HTML_DIR ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
</head>
<body>
<article>
    <h1><?= htmlspecialchars($post['title']) ?></h1>
    <p><?= nl2br(htmlspecialchars($post['body'])) ?></p>
    <hr>
    <small>Created at: <?= date('Y-m-d', strtotime($post['created_at'])) ?></small>
</article>
<a href="/">Back to Home</a>
</body>
</html>