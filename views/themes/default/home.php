<!DOCTYPE html>
<html lang="<?= LANG ?? 'en' ?>" dir="<?= HTML_DIR ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); // Security: Always escape output ?></title>
</head>
<body>
<h1><?php echo htmlspecialchars($welcome); ?></h1>
<p><?php echo htmlspecialchars($welcomeMessage)?></p>
<p>This page was rendered by the View class.</p>
</body>
</html>