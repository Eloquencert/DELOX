<?php

declare(strict_types=1);

$local = __DIR__ . '/database.local.php';
if (file_exists($local)) {
    return require $local;
}

return [
    'host'     => 'localhost',
    'port'     => '3306',
    'database' => 'DB_DELOX',
    'username' => 'root',
    'password' => 'root',
];
