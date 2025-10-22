<?php
// controllers/charts.php (AJAX)
require_once __DIR__ . '/../includes/conexion.php';
if (empty($_SESSION['rol']) || $_SESSION['rol']!=='admin') { http_response_code(403); die('Acceso denegado'); }
header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
if ($type==='ventas30') {
  $res = $mysqli->query("SELECT DATE(creado_en) d, SUM(total) t FROM pedidos WHERE creado_en >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(creado_en) ORDER BY d");
  $labels=[]; $data=[];
  while($r=$res->fetch_assoc()){ $labels[]=$r['d']; $data[]=(float)$r['t']; }
  echo json_encode(['labels'=>$labels,'data'=>$data]); exit;
}
elseif ($type==='top') {
  $res=$mysqli->query("SELECT p.nombre, SUM(pd.cantidad) q FROM pedido_detalles pd JOIN productos p ON p.id=pd.producto_id GROUP BY p.id ORDER BY q DESC LIMIT 5");
  $labels=[]; $data=[];
  while($r=$res->fetch_assoc()){ $labels[]=$r['nombre']; $data[]=(int)$r['q']; }
  echo json_encode(['labels'=>$labels,'data'=>$data]); exit;
}
echo json_encode(['labels'=>[],'data'=>[]]);
