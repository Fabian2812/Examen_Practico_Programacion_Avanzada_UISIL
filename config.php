<?php
declare(strict_types=1);

// Nombre que aparece en el título de todas las páginas.
const APP_NAME = 'Mesa de ayuda UISIL';

// Lee primero una variable de entorno; si no existe, usa la configuración local.
// config.local.php guarda las credenciales reales y se excluye de Git.
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
    // Una sola conexión por petición evita abrir MySQL en cada consulta.
    static $connection = null;
    if ($connection instanceof mysqli) {
        return $connection;
    }

    // mysqli conecta PHP con la base definida en schema.sql.
    $connection = new mysqli(
        db_setting('DB_HOST', '127.0.0.1'),
        db_setting('DB_USER', 'root'),
        db_setting('DB_PASSWORD', ''),
        db_setting('DB_NAME', 'incidencias_uisil'),
        (int) db_setting('DB_PORT', '3306')
    );
    // utf8mb4 conserva tildes, eñes y otros caracteres del formulario.
    $connection->set_charset('utf8mb4');
    return $connection;
}
