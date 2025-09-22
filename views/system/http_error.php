<!DOCTYPE html>
<html lang="<?= LANG ?? 'en' ?>" dir="<?= HTML_DIR ?? 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($error_code) ?> | <?= htmlspecialchars($error_title) ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8f9fa;
            color: #6c757d;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        .container {
            max-width: 600px;
        }
        .error-code {
            font-size: 8rem;
            font-weight: 100;
            line-height: 1;
            color: #e9ecef;
            letter-spacing: .1rem;
        }
        .error-title {
            font-size: 2.5rem;
            font-weight: 300;
            margin-top: -2.5rem; /* Pulls the title up over the code */
            color: #343a40;
        }
        .error-message {
            font-size: 1.25rem;
            font-weight: 300;
            margin-top: 1rem;
            color: #6c757d;
        }
        .home-link {
            margin-top: 2rem;
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: #0d6efd;
            color: #fff;
            text-decoration: none;
            border-radius: 0.3rem;
            font-size: 1rem;
            transition: background-color 0.2s ease-in-out;
        }
        .home-link:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="error-code"><?= htmlspecialchars($error_code) ?></div>
    <div class="error-title"><?= htmlspecialchars($error_title) ?></div>
    <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
    <a href="/" class="home-link">Go to Homepage</a>
</div>
</body>
</html>