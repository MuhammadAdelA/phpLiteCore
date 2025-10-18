<h1><?= htmlspecialchars($pageTitle) ?></h1>
<hr>

<form action="/posts/<?= htmlspecialchars($post->id) ?>" method="POST">
    <div class="mb-3">
        <label for="title" class="form-label"><?= htmlspecialchars($formTitle) ?></label>
        <input type="text" class="form-control" id="title" name="title" required minlength="5" value="<?= htmlspecialchars($post->title) ?>">
    </div>
    <div class="mb-3">
        <label for="body" class="form-label"><?= htmlspecialchars($formContent) ?></label>
        <textarea class="form-control" id="body" name="body" rows="8" required minlength="10"><?= htmlspecialchars($post->body) ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary"><?= htmlspecialchars($updateButton) ?></button>
    <a href="/posts/<?= htmlspecialchars($post->id) ?>" class="btn btn-secondary"><?= htmlspecialchars($cancelButton) ?></a>
</form>