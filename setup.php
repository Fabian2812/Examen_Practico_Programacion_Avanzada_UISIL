<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
$count = (int) db()->query('SELECT COUNT(*) FROM usuarios')->fetch_row()[0];
if ($count > 0) {
    flash('Ya existe un administrador. Inicie sesión.', 'error');
    redirect('login.php');
}
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = is_string($_POST['usuario'] ?? null) ? trim($_POST['usuario']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $confirm = is_string($_POST['confirm'] ?? null) ? $_POST['confirm'] : '';
    if ($username === '' || mb_strlen($username, 'UTF-8') > 80 || strlen($password) < 8) {
        $error = 'Ingrese un usuario y una contraseña de al menos 8 caracteres.';
    } elseif ($password !== $confirm) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = db()->prepare('INSERT INTO usuarios (usuario, password_hash) VALUES (?, ?)');
        $stmt->bind_param('ss', $username, $hash);
        $stmt->execute();
        flash('Administrador creado. Ya puede iniciar sesión.');
        redirect('login.php');
    }
}
render_header('Configurar administrador');
?>
<div class="page-heading centered"><p class="eyebrow">CONFIGURACIÓN INICIAL</p><h1>Crear administrador</h1><p>Esta pantalla solo está disponible mientras no exista ningún usuario.</p></div>
<section class="panel auth-panel">
  <?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="setup.php"><?= csrf_field() ?>
    <label>Usuario<input name="usuario" maxlength="80" required autocomplete="username" value="<?= e($username ?? '') ?>" autofocus></label>
    <label>Contraseña<input name="password" type="password" minlength="8" required autocomplete="new-password"></label>
    <label>Confirmar contraseña<input name="confirm" type="password" minlength="8" required autocomplete="new-password"></label>
    <button class="button button-primary button-full" type="submit">Crear administrador</button>
  </form>
</section>
<?php render_footer(); ?>
