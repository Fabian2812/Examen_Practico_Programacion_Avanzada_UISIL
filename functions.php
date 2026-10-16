<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    if (getenv('SESSION_SAVE_PATH')) {
        session_save_path((string) getenv('SESSION_SAVE_PATH'));
    }
    session_start();
}

const PRIORITIES = ['Baja', 'Media', 'Alta', 'Critica'];
const STATUSES = ['Pendiente', 'En proceso', 'Resuelta'];

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path, true, 303);
    exit;
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token']) ||
        !hash_equals(csrf_token(), $_POST['csrf_token'])) {
        http_response_code(403);
        exit('Solicitud no válida. Vuelva a cargar la página.');
    }
}

function is_admin(): bool
{
    return isset($_SESSION['admin_id']) && is_int($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!is_admin()) {
        flash('Inicie sesión para realizar esta acción.', 'error');
        redirect('login.php');
    }
}

function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function pull_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function incident_id(mixed $value): int
{
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        http_response_code(400);
        exit('Identificador inválido.');
    }
    return $id;
}

function find_incident(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM incidencias WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?: null;
}

function validate_incident(array $source): array
{
    $data = [];
    $limits = ['reportado_por' => 120, 'laboratorio' => 100, 'equipo' => 120, 'descripcion' => 2000];
    foreach ($limits as $key => $limit) {
        $value = $source[$key] ?? '';
        $data[$key] = is_string($value) ? trim($value) : '';
        if ($data[$key] === '' || mb_strlen($data[$key], 'UTF-8') > $limit) {
            throw new InvalidArgumentException('Complete todos los campos. Revise también su longitud.');
        }
    }
    $priority = $source['prioridad'] ?? '';
    if (!is_string($priority) || !in_array($priority, PRIORITIES, true)) {
        throw new InvalidArgumentException('Seleccione una prioridad válida.');
    }
    $data['prioridad'] = $priority;
    return $data;
}

function render_header(string $title): void
{
    $flash = pull_flash();
    $name = e(APP_NAME);
    $titleHtml = e($title);
    $authLink = is_admin()
        ? '<form method="post" action="logout.php" class="inline-form">' . csrf_field() . '<button class="nav-link" type="submit">Cerrar sesión</button></form>'
        : '<a href="login.php">Administración</a>';
    echo "<!doctype html><html lang=\"es\"><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"><title>{$titleHtml} · {$name}</title><link rel=\"stylesheet\" href=\"assets/styles.css\"></head><body>";
    echo '<header class="site-header"><div class="container header-inner"><a class="brand" href="index.php"><span class="brand-mark">U</span><span>Mesa de ayuda <small>UISIL · Soporte tecnológico</small></span></a><nav aria-label="Navegación principal"><a href="index.php">Incidencias</a><a href="create.php">Reportar falla</a>' . $authLink . '</nav></div></header>';
    echo '<main class="container">';
    if ($flash) {
        echo '<div class="alert ' . ($flash['type'] === 'error' ? 'alert-error' : 'alert-success') . '" role="status">' . e($flash['message']) . '</div>';
    }
}

function render_footer(): void
{
    echo '</main><footer class="site-footer"><div class="container">Sistema de gestión de incidencias tecnológicas · UISIL</div></footer></body></html>';
}
