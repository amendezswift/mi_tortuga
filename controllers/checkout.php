<?php
// controllers/checkout.php
require_once __DIR__ . '/../includes/conexion.php';

$action = $_GET['action'] ?? '';
if ($action!=='procesar') { http_response_code(400); die('Acción inválida'); }
if (empty($_SESSION['usuario_id'])) { http_response_code(403); die('Inicie sesión'); }
if (empty($_POST['accept_terms'])) {
  $_SESSION['auth_error'] = 'Debes aceptar los términos antes de completar el pago.';
  header('Location: /mi_tortuga/index.php?page=pago');
  exit;
}

$uid = (int)$_SESSION['usuario_id'];
$metodo_envio = $_POST['metodo_envio'] ?? 'normal';
$metodo_pago  = $_POST['metodo_pago'] ?? 'tarjeta';
$metodo_envio = in_array($metodo_envio, ['normal','expres','gratuito']) ? $metodo_envio : 'normal';
$metodo_pago = in_array($metodo_pago, ['tarjeta','paypal','transferencia']) ? $metodo_pago : 'tarjeta';

// Obtener carrito
$sid = session_id();
$cart = $mysqli->query("SELECT id FROM carritos WHERE usuario_id=$uid OR session_id='$sid' ORDER BY usuario_id IS NULL LIMIT 1")->fetch_assoc();
if (!$cart) {
  $_SESSION['auth_error'] = 'Tu carrito está vacío.';
  header('Location: /mi_tortuga/index.php?page=carrito');
  exit;
}
$cid = (int)$cart['id'];
$items = $mysqli->query("SELECT ci.*, p.precio, p.stock, p.nombre FROM carrito_items ci JOIN productos p ON p.id=ci.producto_id WHERE carrito_id=$cid");

$subtotal=0; $lineas=[];
while ($i=$items->fetch_assoc()){ $lineas[]=$i; $subtotal += $i['cantidad']*$i['precio']; }
if (empty($lineas)) {
  $_SESSION['auth_error'] = 'Tu carrito está vacío.';
  header('Location: /mi_tortuga/index.php?page=carrito');
  exit;
}

$iva = round($subtotal*0.12,2);
switch ($metodo_envio) {
  case 'expres':
    $envio = 45.00;
    break;
  case 'gratuito':
    $envio = 0.00;
    break;
  default:
    $envio = ($subtotal >= 300) ? 0.00 : 25.00;
    break;
}
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

// Crear registro de envío
$tracking = 'ENV' . date('Ymd') . strtoupper(substr(bin2hex(random_bytes(4)),0,6));
$stmtEnv = $mysqli->prepare("INSERT INTO envios(pedido_id, tracking, estado) VALUES (?,?,?)");
$stmtEnv->bind_param('iss', $pedido_id, $tracking, $estado_envio);
$stmtEnv->execute();

// Detalles y rebaja de stock
foreach ($lineas as $ln) {
  $pid=(int)$ln['producto_id']; $cant=(int)$ln['cantidad']; $pu=(float)$ln['precio']; $sub=$cant*$pu;
  // Verificar stock justo antes
  $cur = $mysqli->query("SELECT stock FROM productos WHERE id=$pid")->fetch_assoc();
  if ($cur['stock'] < $cant) {
    $productoNombre = $ln['nombre'] ?? ('#'.$pid);
    $_SESSION['auth_error'] = 'Stock insuficiente para '.$productoNombre.'. Actualiza tu carrito.';
    header('Location: /mi_tortuga/index.php?page=carrito');
    exit;
  }
  $stmt=$mysqli->prepare("INSERT INTO pedido_detalles(pedido_id,producto_id,precio_unitario,cantidad,subtotal) VALUES (?,?,?,?,?)");
  $stmt->bind_param('iidid',$pedido_id,$pid,$pu,$cant,$sub); $stmt->execute();
  $mysqli->query("UPDATE productos SET stock=stock-$cant WHERE id=$pid");
  $stmt=$mysqli->prepare("INSERT INTO inventario_movs(producto_id,cambio,motivo) VALUES (?,?,?)");
  $motivo='Venta orden '.$numero_orden; $chg=-$cant; $stmt->bind_param('iis',$pid,$chg,$motivo); $stmt->execute();
}

// Vaciar carrito
$mysqli->query("DELETE FROM carrito_items WHERE carrito_id=$cid");

// Enviar email de confirmación con PHPMailer si está disponible
$cliente = $mysqli->query("SELECT email,nombre FROM usuarios WHERE id=$uid")->fetch_assoc();
if ($cliente && file_exists(__DIR__.'/../vendor/autoload.php')) {
  require_once __DIR__.'/../vendor/autoload.php';
  try {
    $mailer = new PHPMailer\PHPMailer\PHPMailer(true);
    $mailer->isSMTP();
    $mailer->Host = 'smtp.gmail.com';
    $mailer->SMTPAuth = true;
    $mailer->Username = 'TU_CORREO@gmail.com';
    $mailer->Password = 'TU_APP_PASSWORD';
    $mailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mailer->Port = 587;
    $mailer->CharSet = 'UTF-8';
    $mailer->setFrom('no-reply@mitortuga.com','Mi Tortuga');
    $mailer->addAddress($cliente['email'], $cliente['nombre']);
    $mailer->Subject = 'Confirmación de pedido '.$numero_orden;
    $mailer->Body = "Gracias por tu compra. Número de orden: $numero_orden.\\n".
      "Total: Q " . number_format($total,2) . "\\n".
      "Método de pago: $metodo_pago\\n".
      "Método de envío: $metodo_envio\\n".
      "Número de guía: $tracking";
    $mailer->send();
  } catch (Throwable $e) {
    error_log('No se pudo enviar el correo: '.$e->getMessage());
  }
} else {
  error_log('PHPMailer no instalado o usuario sin correo. Ejecuta composer require phpmailer/phpmailer para habilitar el envío.');
}

header('Location: /mi_tortuga/index.php?page=confirmacion&orden='.$numero_orden);
exit;
