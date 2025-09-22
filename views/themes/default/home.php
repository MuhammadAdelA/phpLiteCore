<!DOCTYPE html>
<html lang="<?= LANG ?? 'en' ?>" dir="<?= HTML_DIR ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle); ?></title>
</head>
<body>
<h1><?= htmlspecialchars($welcomeMessage); ?></h1>
<p><?= htmlspecialchars($PhpLiteCoreIsUpAndRunning); ?></p>
</body>
</html>