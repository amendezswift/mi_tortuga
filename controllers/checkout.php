<?php
// controllers/checkout.php
require_once __DIR__ . '/../includes/conexion.php';

$action = $_GET['action'] ?? '';
if ($action!=='procesar') { http_response_code(400); die('Acción inválida'); }
if (empty($_SESSION['usuario_id'])) { http_response_code(403); die('Inicie sesión'); }

$uid = (int)$_SESSION['usuario_id'];
$metodo_envio = $_POST['metodo_envio'] ?? 'normal';
$metodo_pago  = $_POST['metodo_pago'] ?? 'tarjeta';

// Obtener carrito
$sid = session_id();
$cart = $mysqli->query("SELECT id FROM carritos WHERE usuario_id=$uid OR session_id='$sid' ORDER BY usuario_id IS NULL LIMIT 1")->fetch_assoc();
if (!$cart) { die('Carrito vacío'); }
$cid = (int)$cart['id'];
$items = $mysqli->query("SELECT ci.*, p.precio, p.stock FROM carrito_items ci JOIN productos p ON p.id=ci.producto_id WHERE carrito_id=$cid");

$subtotal=0; $lineas=[];
while ($i=$items->fetch_assoc()){ $lineas[]=$i; $subtotal += $i['cantidad']*$i['precio']; }
if (empty($lineas)) die('Carrito vacío');

$iva = round($subtotal*0.12,2);
$envio = ($subtotal>300)?0:25;
$total = $subtotal + $iva + $envio;

// Simulación de pasarela (SSL simulado)
$numero_orden = 'MT' . date('YmdHis') . rand(100,999);

// Crear pedido
$stmt=$mysqli->prepare("INSERT INTO pedidos(usuario_id,total,impuestos,envio,metodo_envio,metodo_pago,estado_pago,estado_envio,numero_orden) VALUES (?,?,?,?,?,?,?,?,?)");
$estado_pago = 'aprobado';
$estado_envio = 'pendiente';
$stmt->bind_param('idddsssss',$uid,$total,$iva,$envio,$metodo_envio,$metodo_pago,$estado_pago,$estado_envio,$numero_orden);
$stmt->execute();
$pedido_id = $stmt->insert_id;

// Detalles y rebaja de stock
foreach ($lineas as $ln) {
  $pid=(int)$ln['producto_id']; $cant=(int)$ln['cantidad']; $pu=(float)$ln['precio']; $sub=$cant*$pu;
  // Verificar stock justo antes
  $cur = $mysqli->query("SELECT stock FROM productos WHERE id=$pid")->fetch_assoc();
  if ($cur['stock'] < $cant) { die('Stock insuficiente en checkout'); }
  $stmt=$mysqli->prepare("INSERT INTO pedido_detalles(pedido_id,producto_id,precio_unitario,cantidad,subtotal) VALUES (?,?,?,?,?)");
  $stmt->bind_param('iidid',$pedido_id,$pid,$pu,$cant,$sub); $stmt->execute();
  $mysqli->query("UPDATE productos SET stock=stock-$cant WHERE id=$pid");
  $stmt=$mysqli->prepare("INSERT INTO inventario_movs(producto_id,cambio,motivo) VALUES (?,?,?)");
  $motivo='Venta orden '.$numero_orden; $chg=-$cant; $stmt->bind_param('iis',$pid,$chg,$motivo); $stmt->execute();
}

// Vaciar carrito
$mysqli->query("DELETE FROM carrito_items WHERE carrito_id=$cid");

// Enviar email de confirmación (PHPMailer requerido)
/*
require __DIR__.'/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
$mail = new PHPMailer(true);
try {
  $mail->isSMTP();
  $mail->Host = 'smtp.gmail.com'; $mail->SMTPAuth = true;
  $mail->Username = 'TU_CORREO@gmail.com'; $mail->Password = 'TU_APP_PASSWORD';
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; $mail->Port = 587;
  $u = $mysqli->query("SELECT email,nombre FROM usuarios WHERE id=$uid")->fetch_assoc();
  $mail->setFrom('no-reply@mitortuga.com','Mi Tortuga');
  $mail->addAddress($u['email'], $u['nombre']);
  $mail->Subject = 'Confirmación de pedido '.$numero_orden;
  $mail->Body = "Gracias por tu compra. Número de orden: $numero_orden. Total Q $total";
  $mail->send();
} catch(Exception $e) { error_log('Mailer error: '.$e->getMessage()); }
*/

header('Location: /mi_tortuga/index.php?page=confirmacion&orden='.$numero_orden);
exit;
