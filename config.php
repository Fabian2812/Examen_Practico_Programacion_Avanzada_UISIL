<?php
declare(strict_types=1);

// XAMPP normalmente usa root sin contraseña. Estas variables permiten cambiarlo.
const APP_NAME = 'Mesa de ayuda UISIL';

function db_setting(string $name, string $default): string
{
    $environment = getenv($name);
    if ($environment !== false) {
        return (string) $environment;
    }
    $local = is_file(__DIR__ . '/config.local.php') ? require __DIR__ . '/config.local.php' : [];
    return isset($local[$name]) ? (string) $local[$name] : $default;
}

function db(): mysqli
{
    static $connection = null;
    if ($connection instanceof mysqli) {
        return $connection;
    }

    $connection = new mysqli(
        db_setting('DB_HOST', '127.0.0.1'),
        db_setting('DB_USER', 'root'),
        db_setting('DB_PASSWORD', ''),
        db_setting('DB_NAME', 'incidencias_uisil'),
        (int) db_setting('DB_PORT', '3306')
    );
    $connection->set_charset('utf8mb4');
    return $connection;
}
