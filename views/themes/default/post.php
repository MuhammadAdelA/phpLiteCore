<article>
    <h1><?= htmlspecialchars($post->title) ?></h1>
    <p><?= nl2br(htmlspecialchars($post->body)) ?></p>
    <hr>
    <small>Created at: <?= date('Y-m-d', strtotime($post['created_at'])) ?></small>
</article>
<a href="/">Back to Home</a>