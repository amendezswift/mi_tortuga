<?php
// includes/conexion.php
$DB_HOST = '127.0.0.1';
$DB_USER = 'tortuga_user';
$DB_PASS = 'tortuga_pass';
$DB_NAME = 'mi_tortuga';
$DB_PORT = 3306;

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
if ($mysqli->connect_errno) {
    http_response_code(500);
    die('Error de conexión a MySQL: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

session_start();

function is_https() {
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') return true;
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') return true;
    if (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) return true;
    return false;
}
?>
