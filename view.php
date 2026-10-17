<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
// Lee el ID de la URL y carga una sola incidencia de MySQL.
$incident = find_incident(incident_id($_GET['id'] ?? null));
if (!$incident) {
    // El código 404 indica que el registro solicitado no existe.
    http_response_code(404);
    render_header('Incidencia no encontrada');
    echo '<div class="empty-state"><h1>Incidencia no encontrada</h1><a href="index.php">Volver al listado</a></div>';
    render_footer();
    exit;
}
render_header('Incidencia #' . $incident['id']);
?>
<!-- Información pública: problema, persona, laboratorio, prioridad y fechas. -->
<div class="page-heading"><a class="back-link" href="index.php">← Volver al listado</a><p class="eyebrow">DETALLE DE SOLICITUD</p><h1>Incidencia #<?= (int) $incident['id'] ?></h1><p>Registrada el <?= e(date('d/m/Y \a \l\a\s H:i', strtotime($incident['creado_en']))) ?></p></div>
<section class="detail-grid">
  <article class="panel detail-main"><div class="section-heading"><h2><?= e($incident['equipo']) ?></h2><span class="badge status-<?= $incident['estado'] === 'Pendiente' ? 'pending' : ($incident['estado'] === 'Resuelta' ? 'done' : 'progress') ?>"><?= e($incident['estado']) ?></span></div><h3>Descripción del problema</h3><p class="description-full"><?= nl2br(e($incident['descripcion'])) ?></p></article>
  <aside class="panel detail-aside"><h2>Información</h2><dl><dt>Reportado por</dt><dd><?= e($incident['reportado_por']) ?></dd><dt>Laboratorio</dt><dd><?= e($incident['laboratorio']) ?></dd><dt>Prioridad</dt><dd><span class="badge priority-<?= strtolower($incident['prioridad']) ?>"><?= $incident['prioridad'] === 'Critica' ? 'Crítica' : e($incident['prioridad']) ?></span></dd><dt>Última actualización</dt><dd><?= e(date('d/m/Y H:i', strtotime($incident['actualizado_en']))) ?></dd></dl></aside>
</section>
<?php // Los controles siguientes solo se muestran a administradores autenticados. ?>
<?php if (is_admin()): ?>
<section class="panel admin-panel"><div class="section-heading"><div><p class="eyebrow">ADMINISTRACIÓN</p><h2>Gestionar incidencia</h2></div></div>
  <!-- Cada acción POST lleva CSRF. JavaScript pide confirmación antes de eliminar. -->
  <div class="admin-actions"><form method="post" action="status.php" class="status-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $incident['id'] ?>"><label>Cambiar estado<select name="estado"><?php foreach (STATUSES as $item): ?><option value="<?= e($item) ?>" <?= $incident['estado'] === $item ? 'selected' : '' ?>><?= e($item) ?></option><?php endforeach; ?></select></label><button class="button button-primary" type="submit">Guardar estado</button></form><a class="button button-light" href="edit.php?id=<?= (int) $incident['id'] ?>">Editar datos</a><form method="post" action="delete.php" onsubmit="return confirm('¿Eliminar esta incidencia de forma permanente?');"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $incident['id'] ?>"><button class="button button-danger" type="submit">Eliminar</button></form></div>
</section>
<?php endif; ?>
<?php render_footer(); ?>
