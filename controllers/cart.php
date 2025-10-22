<?php
// controllers/cart.php (AJAX)
require_once __DIR__ . '/../includes/conexion.php';
header('Content-Type: application/json');

function get_or_create_cart($mysqli) {
  $sid = session_id();
  $uid = $_SESSION['usuario_id'] ?? null;
  if ($uid) {
    $stmt=$mysqli->prepare("SELECT id FROM carritos WHERE usuario_id=?");
    $stmt->bind_param('i',$uid); $stmt->execute(); $res=$stmt->get_result()->fetch_assoc();
    if ($res) return (int)$res['id'];
    $stmt=$mysqli->prepare("INSERT INTO carritos(usuario_id) VALUES (?)"); $stmt->bind_param('i',$uid); $stmt->execute(); return $stmt->insert_id;
  } else {
    $stmt=$mysqli->prepare("SELECT id FROM carritos WHERE session_id=?");
    $stmt->bind_param('s',$sid); $stmt->execute(); $res=$stmt->get_result()->fetch_assoc();
    if ($res) return (int)$res['id'];
    $stmt=$mysqli->prepare("INSERT INTO carritos(session_id) VALUES (?)"); $stmt->bind_param('s',$sid); $stmt->execute(); return $stmt->insert_id;
  }
}

$action = $_GET['action'] ?? '';
$cart_id = get_or_create_cart($mysqli);

if ($action==='add') {
  $pid = (int)($_POST['producto_id'] ?? 0);
  $qty = max(1, (int)($_POST['cantidad'] ?? 1));
  // Verificar stock
  $ps = $mysqli->query("SELECT stock FROM productos WHERE id=$pid")->fetch_assoc();
  if (!$ps || $ps['stock'] < $qty) { echo json_encode(['ok'=>false,'msg'=>'Stock insuficiente']); exit; }
  // Insert/update
  $stmt=$mysqli->prepare("INSERT INTO carrito_items(carrito_id,producto_id,cantidad) VALUES (?,?,?) ON DUPLICATE KEY UPDATE cantidad=cantidad+VALUES(cantidad)");
  $stmt->bind_param('iii',$cart_id,$pid,$qty); $stmt->execute();
  echo json_encode(['ok'=>true]); exit;
}
elseif ($action==='remove') {
  $pid = (int)($_POST['producto_id'] ?? 0);
  $mysqli->query("DELETE FROM carrito_items WHERE carrito_id=$cart_id AND producto_id=$pid");
  echo json_encode(['ok'=>true]); exit;
}
elseif ($action==='list') {
  $items = $mysqli->query("SELECT ci.producto_id, ci.cantidad, p.nombre, p.precio FROM carrito_items ci JOIN productos p ON p.id=ci.producto_id WHERE ci.carrito_id=$cart_id");
  $arr=[]; $subtotal=0;
  while ($i=$items->fetch_assoc()) { $line=$i['cantidad']*$i['precio']; $subtotal+=$line; $arr[]=$i+['subtotal'=>$line]; }
  $iva = round($subtotal*0.12, 2);
  $envio = ($subtotal>300)?0:25;
  $total = $subtotal + $iva + $envio;
  echo json_encode(['ok'=>true,'items'=>$arr,'subtotal'=>$subtotal,'iva'=>$iva,'envio'=>$envio,'total'=>$total]); exit;
}
