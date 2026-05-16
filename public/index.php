<?php

declare(strict_types=1);

// ─── Path constants ───────────────────────────────────────────────────────────
define('ROOT_PATH',    dirname(__DIR__));
define('APP_PATH',     ROOT_PATH . '/app');
define('CONFIG_PATH',  ROOT_PATH . '/config');
define('ROUTES_PATH',  ROOT_PATH . '/routes');
define('VIEWS_PATH',   ROOT_PATH . '/views');
define('STORAGE_PATH', ROOT_PATH . '/storage');

// ─── Autoloader (PSR-4: App\ → app/) ─────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $prefix  = 'App\\';
    $baseDir = APP_PATH . '/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// ─── App config ───────────────────────────────────────────────────────────────
$config = require CONFIG_PATH . '/app.php';
date_default_timezone_set($config['timezone']);

// ─── Error display (disable in production) ────────────────────────────────────
if ($config['debug']) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

// ─── Session ──────────────────────────────────────────────────────────────────
session_start();

// ─── Bootstrap & run ─────────────────────────────────────────────────────────
$app = new \App\Core\Application();
$app->run();
