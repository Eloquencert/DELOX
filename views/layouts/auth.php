<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'DELOX') ?> — DELOX</title>
    <link rel="stylesheet" href="/DELOX/public/css/app.css">
</head>
<body class="auth-body">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-logo">
                <span class="auth-logo-icon">💬</span>
                <span class="auth-logo-name">DELOX</span>
            </div>
            <?= $content ?>
        </div>
    </div>
    <script src="/DELOX/public/js/app.js"></script>
</body>
</html>
