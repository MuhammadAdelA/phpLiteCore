<article>
    <h1><?= htmlspecialchars($post->title) ?></h1>
    <p><?= nl2br(htmlspecialchars($post->body)) ?></p>
    <hr>

    <small><?= htmlspecialchars($publishedOnText) ?> <?= date('Y-m-d', strtotime($post->created_at)) ?></small>

</article>
<a href="/posts"><?= htmlspecialchars($backLinkText) ?></a>