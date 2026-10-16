<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método no permitido.'); }
verify_csrf();
$id = incident_id($_POST['id'] ?? null);
$stmt = db()->prepare('DELETE FROM incidencias WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
flash($stmt->affected_rows ? 'Incidencia eliminada correctamente.' : 'La incidencia ya no existe.');
redirect('index.php');
