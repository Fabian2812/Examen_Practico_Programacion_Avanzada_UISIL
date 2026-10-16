<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
require_admin();
$id = incident_id($_GET['id'] ?? $_POST['id'] ?? null);
$incident = find_incident($id);
if (!$incident) {
    http_response_code(404);
    exit('Incidencia no encontrada.');
}
$data = $incident;
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach (['reportado_por', 'laboratorio', 'equipo', 'descripcion', 'prioridad'] as $key) {
        $data[$key] = is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : '';
    }
    try {
        $valid = validate_incident($_POST);
        $stmt = db()->prepare('UPDATE incidencias SET reportado_por = ?, laboratorio = ?, equipo = ?, descripcion = ?, prioridad = ? WHERE id = ?');
        $stmt->bind_param('sssssi', $valid['reportado_por'], $valid['laboratorio'], $valid['equipo'], $valid['descripcion'], $valid['prioridad'], $id);
        $stmt->execute();
        flash('Incidencia actualizada correctamente.');
        redirect('view.php?id=' . $id);
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
    }
}
render_header('Editar incidencia');
?>
<div class="page-heading"><a class="back-link" href="view.php?id=<?= $id ?>">← Volver al detalle</a><p class="eyebrow">ADMINISTRACIÓN</p><h1>Editar incidencia #<?= $id ?></h1><p>Actualice los datos de la solicitud.</p></div>
<section class="panel form-panel">
  <?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="edit.php?id=<?= $id ?>">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
    <div class="form-grid">
      <label>Nombre de quien reporta <span>*</span><input name="reportado_por" maxlength="120" required value="<?= e($data['reportado_por']) ?>"></label>
      <label>Laboratorio <span>*</span><input name="laboratorio" maxlength="100" required value="<?= e($data['laboratorio']) ?>"></label>
      <label>Equipo afectado <span>*</span><input name="equipo" maxlength="120" required value="<?= e($data['equipo']) ?>"></label>
      <label>Prioridad <span>*</span><select name="prioridad" required><?php foreach (PRIORITIES as $item): ?><option value="<?= e($item) ?>" <?= $data['prioridad'] === $item ? 'selected' : '' ?>><?= $item === 'Critica' ? 'Crítica' : e($item) ?></option><?php endforeach; ?></select></label>
      <label class="full-width">Descripción del problema <span>*</span><textarea name="descripcion" maxlength="2000" rows="6" required><?= e($data['descripcion']) ?></textarea></label>
    </div>
    <div class="form-actions"><button class="button button-primary" type="submit">Guardar cambios</button><a class="button button-light" href="view.php?id=<?= $id ?>">Cancelar</a></div>
  </form>
</section>
<?php render_footer(); ?>
