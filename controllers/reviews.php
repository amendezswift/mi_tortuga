<?php
// controllers/reviews.php
require_once __DIR__ . '/../includes/conexion.php';

if (empty($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'msg' => 'Debes iniciar sesión para opinar.']);
    return;
}

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

if ($action === 'create') {
    $producto_id = (int)($_POST['producto_id'] ?? 0);
    $calificacion = (int)($_POST['calificacion'] ?? 0);
    $comentario = trim($_POST['comentario'] ?? '');
    if ($producto_id <= 0 || $calificacion < 1 || $calificacion > 5) {
        echo json_encode(['ok' => false, 'msg' => 'Datos inválidos.']);
        return;
    }
    $stmt = $mysqli->prepare('SELECT id FROM productos WHERE id=?');
    $stmt->bind_param('i', $producto_id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_assoc()) {
        echo json_encode(['ok' => false, 'msg' => 'Producto no encontrado.']);
        return;
    }
    $uid = (int)$_SESSION['usuario_id'];
    $stmt = $mysqli->prepare('INSERT INTO resenas(producto_id, usuario_id, calificacion, comentario) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE calificacion=VALUES(calificacion), comentario=VALUES(comentario), creado_en=NOW()');
    $stmt->bind_param('iiis', $producto_id, $uid, $calificacion, $comentario);
    $stmt->execute();
    $stmt = $mysqli->prepare('UPDATE productos SET popularidad = (SELECT IFNULL(SUM(calificacion),0) FROM resenas WHERE producto_id=?) WHERE id=?');
    $stmt->bind_param('ii', $producto_id, $producto_id);
    $stmt->execute();
    echo json_encode(['ok' => true]);
    return;
}

echo json_encode(['ok' => false, 'msg' => 'Acción no soportada']);
