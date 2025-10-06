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