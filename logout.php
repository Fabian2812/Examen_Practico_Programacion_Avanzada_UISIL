<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
// Cerrar sesión se hace por POST con CSRF, no mediante un enlace GET.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método no permitido.'); }
verify_csrf();
// Borra la identidad administrativa y renueva el identificador de sesión.
$_SESSION = [];
session_regenerate_id(true);
flash('Sesión cerrada.');
redirect('index.php');
