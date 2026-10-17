<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
// Acción administrativa: el botón de view.php pide confirmación antes de enviar.
require_admin();
// El servidor exige POST y CSRF aunque alguien intente omitir JavaScript.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método no permitido.'); }
verify_csrf();
$id = incident_id($_POST['id'] ?? null);
// La consulta preparada elimina únicamente la fila identificada por ese ID.
$stmt = db()->prepare('DELETE FROM incidencias WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
flash($stmt->affected_rows ? 'Incidencia eliminada correctamente.' : 'La incidencia ya no existe.');
redirect('index.php');
