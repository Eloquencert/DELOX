<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'DELOX') ?></title>
    <link rel="stylesheet" href="/DELOX/public/css/app.css">
</head>
<body>
    <?= $content ?>
    <script src="/DELOX/public/js/app.js"></script>
</body>
</html>
