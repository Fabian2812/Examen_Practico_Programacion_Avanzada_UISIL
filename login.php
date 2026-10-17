<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
// Si ya hay sesión, vuelve al listado; si no hay cuentas, abre la instalación.
if (is_admin()) redirect('index.php');
$count = (int) db()->query('SELECT COUNT(*) FROM usuarios')->fetch_row()[0];
if ($count === 0) redirect('setup.php');
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Busca el usuario mediante SQL preparado y compara la contraseña con su hash.
    verify_csrf();
    $username = is_string($_POST['usuario'] ?? null) ? trim($_POST['usuario']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $stmt = db()->prepare('SELECT id, usuario, password_hash FROM usuarios WHERE usuario = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user && password_verify($password, $user['password_hash'])) {
        // Renueva el ID de sesión y recuerda al administrador autenticado.
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $user['id'];
        $_SESSION['admin_name'] = $user['usuario'];
        flash('Sesión iniciada correctamente.');
        redirect('index.php');
    }
    $error = 'Usuario o contraseña incorrectos.';
}
render_header('Administración');
?>
<!-- El formulario solo solicita las credenciales, nunca muestra el hash almacenado. -->
<div class="page-heading centered"><p class="eyebrow">ACCESO RESTRINGIDO</p><h1>Administración</h1><p>Inicie sesión para editar, eliminar o cambiar el estado de las incidencias.</p></div>
<section class="panel auth-panel">
  <?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="login.php"><?= csrf_field() ?>
    <label>Usuario<input name="usuario" required autocomplete="username" value="<?= e($username ?? '') ?>" autofocus></label>
    <label>Contraseña<input name="password" type="password" required autocomplete="current-password"></label>
    <button class="button button-primary button-full" type="submit">Iniciar sesión</button>
  </form>
</section>
<?php render_footer(); ?>
