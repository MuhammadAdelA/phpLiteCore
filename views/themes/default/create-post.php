<h1><?= htmlspecialchars($pageTitle) ?></h1>
<hr>

<form action="/posts" method="POST">
    <div class="mb-3">
        <label for="title" class="form-label">Post Title</label>
        <input type="text" class="form-control" id="title" name="title" required minlength="5">
    </div>
    <div class="mb-3">
        <label for="body" class="form-label">Post Content</label>
        <textarea class="form-control" id="body" name="body" rows="8" required minlength="10"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Create Post</button>
    <a href="/posts" class="btn btn-secondary">Cancel</a>
</form>