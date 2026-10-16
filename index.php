<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$search = trim((string) ($_GET['q'] ?? ''));
$lab = trim((string) ($_GET['laboratorio'] ?? ''));
$status = (string) ($_GET['estado'] ?? '');
$priority = (string) ($_GET['prioridad'] ?? '');
if (!in_array($status, STATUSES, true)) $status = '';
if (!in_array($priority, PRIORITIES, true)) $priority = '';

$labs = db()->query('SELECT DISTINCT laboratorio FROM incidencias ORDER BY laboratorio')->fetch_all(MYSQLI_ASSOC);
$sql = 'SELECT id, reportado_por, laboratorio, equipo, descripcion, prioridad, estado, creado_en FROM incidencias WHERE 1=1';
$params = [];
$types = '';
if ($search !== '') {
    $sql .= ' AND (reportado_por LIKE ? OR equipo LIKE ? OR descripcion LIKE ?)';
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like);
    $types .= 'sss';
}
if ($lab !== '') {
    $sql .= ' AND laboratorio = ?';
    $params[] = $lab;
    $types .= 's';
}
if ($status !== '') {
    $sql .= ' AND estado = ?';
    $params[] = $status;
    $types .= 's';
}
if ($priority !== '') {
    $sql .= ' AND prioridad = ?';
    $params[] = $priority;
    $types .= 's';
}
$sql .= ' ORDER BY creado_en DESC, id DESC';
$stmt = db()->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$incidents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$totals = ['total' => 0, 'pendientes' => 0, 'proceso' => 0, 'resueltas' => 0];
$summary = db()->query("SELECT COUNT(*) total, SUM(estado = 'Pendiente') pendientes, SUM(estado = 'En proceso') proceso, SUM(estado = 'Resuelta') resueltas FROM incidencias")->fetch_assoc();
foreach ($totals as $key => $_) $totals[$key] = (int) ($summary[$key] ?? 0);

render_header('Incidencias');
?>
<section class="hero">
  <div><p class="eyebrow">CENTRO DE SOPORTE</p><h1>Incidencias tecnológicas</h1><p>Registre una falla y consulte el avance de todas las solicitudes del campus.</p></div>
  <a class="button button-primary" href="create.php">+ Nueva incidencia</a>
</section>

<section class="stats" aria-label="Resumen de incidencias">
  <div class="stat"><span>Total</span><strong><?= $totals['total'] ?></strong></div>
  <div class="stat"><span>Pendientes</span><strong><?= $totals['pendientes'] ?></strong></div>
  <div class="stat"><span>En proceso</span><strong><?= $totals['proceso'] ?></strong></div>
  <div class="stat"><span>Resueltas</span><strong><?= $totals['resueltas'] ?></strong></div>
</section>

<section class="panel filters-panel">
  <div class="section-heading"><div><p class="eyebrow">CONSULTA</p><h2>Buscar incidencias</h2></div><span><?= count($incidents) ?> resultado(s)</span></div>
  <form method="get" class="filter-grid">
    <label>Buscar por texto<input name="q" value="<?= e($search) ?>" placeholder="Persona, equipo o descripción"></label>
    <label>Laboratorio<select name="laboratorio"><option value="">Todos</option><?php foreach ($labs as $row): ?><option value="<?= e($row['laboratorio']) ?>" <?= $lab === $row['laboratorio'] ? 'selected' : '' ?>><?= e($row['laboratorio']) ?></option><?php endforeach; ?></select></label>
    <label>Estado<select name="estado"><option value="">Todos</option><?php foreach (STATUSES as $item): ?><option value="<?= e($item) ?>" <?= $status === $item ? 'selected' : '' ?>><?= e($item) ?></option><?php endforeach; ?></select></label>
    <label>Prioridad<select name="prioridad"><option value="">Todas</option><?php foreach (PRIORITIES as $item): ?><option value="<?= e($item) ?>" <?= $priority === $item ? 'selected' : '' ?>><?= $item === 'Critica' ? 'Crítica' : e($item) ?></option><?php endforeach; ?></select></label>
    <div class="filter-actions"><button class="button button-primary" type="submit">Filtrar</button><a class="button button-light" href="index.php">Limpiar</a></div>
  </form>
</section>

<section class="panel list-panel">
  <div class="section-heading"><div><p class="eyebrow">REGISTRO</p><h2>Listado de incidencias</h2></div></div>
  <?php if (!$incidents): ?><div class="empty-state"><strong>No hay incidencias para mostrar.</strong><p>Pruebe otros filtros o registre una nueva solicitud.</p></div>
  <?php else: ?><div class="table-wrap"><table><thead><tr><th>ID / Fecha</th><th>Incidencia</th><th>Ubicación</th><th>Prioridad</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
  <?php foreach ($incidents as $incident): ?>
    <tr>
      <td><strong>#<?= (int) $incident['id'] ?></strong><small><?= e(date('d/m/Y H:i', strtotime($incident['creado_en']))) ?></small></td>
      <td><strong><?= e($incident['equipo']) ?></strong><small><?= e($incident['reportado_por']) ?></small><p class="description-preview"><?= e(mb_strimwidth($incident['descripcion'], 0, 100, '…', 'UTF-8')) ?></p></td>
      <td><?= e($incident['laboratorio']) ?></td>
      <td><span class="badge priority-<?= strtolower($incident['prioridad']) ?>"><?= $incident['prioridad'] === 'Critica' ? 'Crítica' : e($incident['prioridad']) ?></span></td>
      <td><span class="badge status-<?= $incident['estado'] === 'Pendiente' ? 'pending' : ($incident['estado'] === 'Resuelta' ? 'done' : 'progress') ?>"><?= e($incident['estado']) ?></span></td>
      <td class="actions"><a href="view.php?id=<?= (int) $incident['id'] ?>">Ver</a><?php if (is_admin()): ?><a href="edit.php?id=<?= (int) $incident['id'] ?>">Editar</a><?php endif; ?></td>
    </tr>
  <?php endforeach; ?></tbody></table></div><?php endif; ?>
</section>
<?php render_footer(); ?>
