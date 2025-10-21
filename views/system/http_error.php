<!DOCTYPE html>
<html lang="<?= LANG ?? 'en' ?>" dir="<?= HTML_DIR ?? 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($error_code) ?> | <?= e($error_title) ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f4f7f9;
            color: #525f7f;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        .card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            padding: 40px 50px;
            max-width: 500px;
            width: 100%;
        }
        .illustration {
            width: 150px;
            margin-bottom: 20px;
        }
        .illustration-404 path { fill: #ffc107; }
        .illustration-500 path { fill: #f5365c; }

        .error-code {
            font-size: 5rem;
            font-weight: 100;
            line-height: 1;
            color: #dee2e6;
            margin: 0;
        }
        .error-title {
            font-size: 2rem;
            font-weight: 600;
            margin-top: -1.5rem;
            color: #32325d;
        }
        /* --- Style Changes Start --- */
        .error-message {
            font-size: 1.1rem;
            font-weight: 400;
            margin-top: 20px;
            color: #6c757d; /* Slightly darker color for better readability */
            line-height: 1.6; /* Increased line height for better flow */
        }
        /* --- Style Changes End --- */
        .home-link {
            margin-top: 30px;
            display: inline-block;
            padding: 12px 25px;
            background-color: #5e72e4;
            color: #fff;
            text-decoration: none;
            border-radius: 30px;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        .home-link:hover {
            transform: translateY(-1px);
            background-color: #4a5cc5;
            box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
        }
    </style>
</head>
<body>
<div class="card">

    <?php if ($error_code == 404): ?>
        <svg class="illustration illustration-404" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" opacity="0.3"/>
        </svg>
    <?php else: ?>
        <svg class="illustration illustration-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z" fill="#f5365c"/>
        </svg>
    <?php endif; ?>

    <h1 class="error-code"><?= e($error_code) ?></h1>
    <h2 class="error-title"><?= e($error_title) ?></h2>
    <p class="error-message">
        <?= e($error_message) ?>
    </p>

    <a href="/" class="home-link"><?= e($homeLinkText) ?></a>
</div>
</body>
</html>