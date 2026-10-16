<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método no permitido.'); }
verify_csrf();
$id = incident_id($_POST['id'] ?? null);
$status = $_POST['estado'] ?? null;
if (!is_string($status) || !in_array($status, STATUSES, true)) { http_response_code(400); exit('Estado inválido.'); }
$stmt = db()->prepare('UPDATE incidencias SET estado = ? WHERE id = ?');
$stmt->bind_param('si', $status, $id);
$stmt->execute();
flash($stmt->affected_rows ? 'Estado actualizado correctamente.' : 'No hubo cambios en el estado.');
redirect('view.php?id=' . $id);
