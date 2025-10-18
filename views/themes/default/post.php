<article>
    <h1><?= htmlspecialchars($post->title) ?></h1>
    <p><?= nl2br(htmlspecialchars($post->body)) ?></p>
    <hr>
    <small><?= htmlspecialchars($publishedOnText) ?> <?= date('Y-m-d', strtotime($post->created_at)) ?></small>

    <div class="mt-3">
        <a href="/posts/<?= htmlspecialchars($post->id) ?>/edit" class="btn btn-sm btn-outline-secondary">
            <?= htmlspecialchars($editButtonText ?? 'Edit') ?> </a>
    </div>
</article>

<a href="/posts" class="mt-4 d-inline-block"><?= htmlspecialchars($backLinkText) ?></a>