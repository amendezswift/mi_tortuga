<?php
// controllers/chat.php (AJAX)
require_once __DIR__ . '/../includes/conexion.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
if ($action==='send') {
  $mensaje = trim($_POST['mensaje'] ?? '');
  $nombre = trim($_POST['nombre'] ?? '');
  if (!$mensaje) { echo json_encode(['ok'=>false]); exit; }
  $uid = $_SESSION['usuario_id'] ?? null;
  $stmt=$mysqli->prepare("INSERT INTO chat_mensajes(usuario_id,nombre,mensaje,es_admin,sala) VALUES (?,?,?,?,?)");
  $admin = (!empty($_SESSION['rol']) && $_SESSION['rol']==='admin')?1:0;
  $sala='publico';
  $stmt->bind_param('issis',$uid,$nombre,$mensaje,$admin,$sala); $stmt->execute();
  echo json_encode(['ok'=>true]); exit;
}
elseif ($action==='list') {
  $last = (int)($_GET['last'] ?? 0);
  $res = $mysqli->query("SELECT id, nombre, mensaje, es_admin, creado_en FROM chat_mensajes WHERE id>$last ORDER BY id ASC LIMIT 100");
  $msgs=[]; $maxid=$last;
  while($m=$res->fetch_assoc()) { $msgs[]=$m; if ($m['id']>$maxid) $maxid=$m['id']; }
  echo json_encode(['ok'=>true,'last'=>$maxid,'mensajes'=>$msgs]); exit;
}
