<?php
require_once __DIR__ . '/includes/conexion.php';
include __DIR__ . '/includes/header.php';

$page = $_GET['page'] ?? 'home';
$allowed = ['home','productos','carrito','perfil','login','registro','admin','soporte','faq','privacidad','terminos','pago','confirmacion'];
if (!in_array($page, $allowed)) $page = 'home';

include __DIR__ . '/views/' . $page . '.php';

include __DIR__ . '/includes/footer.php';
?>
