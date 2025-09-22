<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uncaught Exception: <?= htmlspecialchars($exception_class) ?></title>
    <style>
        /* CSS styles remain the same */
        body {
            font-family: 'Segoe UI', 'Roboto', sans-serif;
            background-color: #1a1a1a;
            color: #e8e8e8;
            margin: 0;
            padding: 2rem;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            border: 1px solid #444;
            border-radius: 8px;
            background-color: #2c2c2c;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
        }
        .header {
            background-color: #c0392b;
            color: #ffffff;
            padding: 1rem 1.5rem;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            border-bottom: 1px solid #444;
        }
        .header h1 { margin: 0; font-size: 1.5rem; }
        .header h3 { margin: 0.25rem 0 0 0; font-weight: normal; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 1.5rem; font-size: 1.1rem; line-height: 1.7; }
        .content p { margin: 0 0 1rem 0; }
        .content b { color: #e74c3c; font-weight: 600; }
        .stack-trace {
            background-color: #1e1e1e;
            border: 1px solid #444;
            padding: 1rem;
            border-radius: 5px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.9rem;
            color: #dcdcdc;
            white-space: pre;
            overflow-x: auto;
        }
        .footer { padding: 0.5rem 1.5rem; font-size: 0.8rem; text-align: center; color: #777; border-top: 1px solid #444; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Uncaught Exception</h1>
        <h3><?= htmlspecialchars($exception_class) ?></h3>
    </div>
    <div class="content">
        <p><b>Message:</b> <?= htmlspecialchars($message) ?></p>
        <p><b>File:</b> <?= htmlspecialchars($file) ?> on line <b><?= $line ?></b></p>
        <h3>Stack Trace:</h3>
        <pre class="stack-trace"><?= htmlspecialchars($trace) ?></pre>
    </div>
    <div class="footer">
        <p>phpLiteCore Error Handler</p>
    </div>
</div>
</body>
</html>