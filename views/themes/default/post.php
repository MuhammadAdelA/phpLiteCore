<article>
    <h1><?= e($post->title) ?></h1>
    <p><?= nl2br(e($post->body)) ?></p>
    <hr>
    <small><?= e($publishedOnText) ?> <?= date('Y-m-d', strtotime($post->created_at)) ?></small>

    <div class="mt-3">
        <a href="/posts/<?= e($post->id) ?>/edit" class="btn btn-sm btn-outline-secondary">
            <?= e($editButtonText ?? 'Edit') ?> </a>
    </div>
</article>

<a href="/posts" class="mt-4 d-inline-block"><?= e($backLinkText) ?></a>