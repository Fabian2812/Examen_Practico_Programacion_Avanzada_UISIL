<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
// Formulario público: cualquier persona puede reportar una falla.
$errors = [];
$data = ['reportado_por' => '', 'laboratorio' => '', 'equipo' => '', 'descripcion' => '', 'prioridad' => 'Media'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST: valida el token y los campos antes de guardar en MySQL.
    verify_csrf();
    foreach ($data as $key => $_) $data[$key] = is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : '';
    try {
        $valid = validate_incident($_POST);
        $stmt = db()->prepare('INSERT INTO incidencias (reportado_por, laboratorio, equipo, descripcion, prioridad) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $valid['reportado_por'], $valid['laboratorio'], $valid['equipo'], $valid['descripcion'], $valid['prioridad']);
        $stmt->execute();
        // Se abre el detalle del nuevo registro para confirmar que se guardó.
        flash('Incidencia registrada correctamente.');
        redirect('view.php?id=' . db()->insert_id);
    } catch (InvalidArgumentException $error) {
        $errors[] = $error->getMessage();
    }
}
render_header('Nueva incidencia');
?>
<!-- Formulario de alta: required ayuda en el navegador; validate_incident protege el servidor. -->
<div class="page-heading"><a class="back-link" href="index.php">← Volver al listado</a><p class="eyebrow">NUEVA SOLICITUD</p><h1>Reportar una falla</h1><p>Describa el problema para que el equipo de soporte pueda darle seguimiento.</p></div>
<section class="panel form-panel">
  <?php foreach ($errors as $error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endforeach; ?>
  <form method="post" action="create.php">
    <?= csrf_field() ?>
    <div class="form-grid">
      <label>Nombre de quien reporta <span>*</span><input name="reportado_por" maxlength="120" required value="<?= e($data['reportado_por']) ?>" autofocus></label>
      <label>Laboratorio <span>*</span><input name="laboratorio" maxlength="100" required value="<?= e($data['laboratorio']) ?>" placeholder="Ej. Laboratorio 1"></label>
      <label>Equipo afectado <span>*</span><input name="equipo" maxlength="120" required value="<?= e($data['equipo']) ?>" placeholder="Ej. PC-05 o proyector"></label>
      <label>Prioridad <span>*</span><select name="prioridad" required><?php foreach (PRIORITIES as $item): ?><option value="<?= e($item) ?>" <?= $data['prioridad'] === $item ? 'selected' : '' ?>><?= $item === 'Critica' ? 'Crítica' : e($item) ?></option><?php endforeach; ?></select></label>
      <label class="full-width">Descripción del problema <span>*</span><textarea name="descripcion" maxlength="2000" rows="6" required placeholder="Explique qué ocurre y desde cuándo."><?= e($data['descripcion']) ?></textarea></label>
    </div>
    <div class="form-actions"><button class="button button-primary" type="submit">Registrar incidencia</button><a class="button button-light" href="index.php">Cancelar</a></div>
  </form>
</section>
<?php render_footer(); ?>
