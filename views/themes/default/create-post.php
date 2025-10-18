<h1><?= htmlspecialchars($pageTitle) ?></h1>
<hr>

<form action="/posts" method="POST">
    <div class="mb-3">
        <label for="title" class="form-label"><?= htmlspecialchars($formTitle) ?></label>
        <input type="text" class="form-control" id="title" name="title" required minlength="5">
    </div>
    <div class="mb-3">
        <label for="body" class="form-label"><?= htmlspecialchars($formContent) ?></label>
        <textarea class="form-control" id="body" name="body" rows="8" required minlength="10"></textarea>
    </div>
    <button type="submit" class="btn btn-primary"><?= htmlspecialchars($createButton) ?></button>
    <a href="/posts" class="btn btn-secondary"><?= htmlspecialchars($cancelButton) ?></a>
</form>