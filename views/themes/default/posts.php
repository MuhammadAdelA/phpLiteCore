<!DOCTYPE html>
<html lang="<?= LANG ?? 'en' ?>" dir="<?= HTML_DIR ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 2rem; }
        .post-item { margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #eee; }
        .post-item h2 a { text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <h1><?= htmlspecialchars($pageTitle) ?></h1>
    <hr>

    <?php if (empty($posts)): ?>
        <p>No posts found.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <article class="post-item">
                <h2>
                    <a href="/posts/<?= htmlspecialchars($post['id']) ?>">
                        <?= htmlspecialchars($post['title']) ?>
                    </a>
                </h2>
                <p><?= htmlspecialchars(substr($post['body'], 0, 150)) ?>...</p>
                <small>Published on: <?= date('F j, Y', strtotime($post['created_at'])) ?></small>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="mt-4">
        <?= $paginationLinks ?>
    </div>
</div>
</body>
</html>