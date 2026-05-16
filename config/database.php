<?php

declare(strict_types=1);

// If a local override file exists, use it (excluded from Git via .gitignore)
$local = __DIR__ . '/database.local.php';
if (file_exists($local)) {
    return require $local;
}

return [
    'host'     => 'localhost',
    'port'     => '3306',
    'database' => 'delox_messenger',
    'username' => 'root',
    'password' => 'root',
];
