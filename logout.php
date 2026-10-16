<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método no permitido.'); }
verify_csrf();
$_SESSION = [];
session_regenerate_id(true);
flash('Sesión cerrada.');
redirect('index.php');
