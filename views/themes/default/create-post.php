<h1><?= e($pageTitle) ?></h1>
<hr>

<form action="/posts" method="POST">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label for="title" class="form-label"><?= e($formTitle) ?></label>
        <input type="text" class="form-control" id="title" name="title" required minlength="5">
    </div>
    <div class="mb-3">
        <label for="body" class="form-label"><?= e($formContent) ?></label>
        <textarea class="form-control" id="body" name="body" rows="8" required minlength="10"></textarea>
    </div>
    <button type="submit" class="btn btn-primary"><?= e($createButton) ?></button>
    <a href="/posts" class="btn btn-secondary"><?= e($cancelButton) ?></a>
</form>