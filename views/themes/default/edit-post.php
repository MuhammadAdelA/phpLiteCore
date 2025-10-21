<h1><?= e($pageTitle) ?></h1>
<hr>

<form action="/posts/<?= e($post->id) ?>" method="POST">
    <div class="mb-3">
        <label for="title" class="form-label"><?= e($formTitle) ?></label>
        <input type="text" class="form-control" id="title" name="title" required minlength="5" value="<?= e($post->title) ?>">
    </div>
    <div class="mb-3">
        <label for="body" class="form-label"><?= e($formContent) ?></label>
        <textarea class="form-control" id="body" name="body" rows="8" required minlength="10"><?= e($post->body) ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary"><?= e($updateButton) ?></button>
    <a href="/posts/<?= e($post->id) ?>" class="btn btn-secondary"><?= e($cancelButton) ?></a>
</form>