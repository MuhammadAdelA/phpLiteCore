<div class="container">
    <h1><?= e($pageTitle) ?></h1>
    <hr>

    <?php if (empty($posts)): ?>
        <p><?= e($noPostsText) ?></p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <article class="post-item">
                <h2>
                    <a href="/posts/<?= e($post->id) ?>">
                        <?= e($post->title) ?>
                    </a>
                </h2>
                <p><?= e(substr($post->body, 0, 150)) ?>...</p>
                <small><?= e($publishedOnText) ?> <?= date('F j, Y', strtotime($post->created_at)) ?></small>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="mt-4">
        <?= $paginationLinks ?>
    </div>
</div>