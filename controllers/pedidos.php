<?php
// controllers/pedidos.php
require_once __DIR__ . '/../includes/conexion.php';

if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    exit('Acceso denegado');
}

$action = $_GET['action'] ?? '';

function sanitize_date(string $value, string $default): string {
    $dt = DateTime::createFromFormat('Y-m-d', $value);
    return $dt ? $dt->format('Y-m-d') : $default;
}

if ($action === 'update_envio') {
    $pedido_id = (int)($_POST['pedido_id'] ?? 0);
    $estado = $_POST['estado_envio'] ?? 'pendiente';
    $tracking = trim($_POST['tracking'] ?? '');
    $fromPost = sanitize_date($_POST['from'] ?? date('Y-m-01'), date('Y-m-01'));
    $toPost = sanitize_date($_POST['to'] ?? date('Y-m-d'), date('Y-m-d'));
    if ($pedido_id <= 0) {
        $_SESSION['admin_flash'] = 'Pedido inválido.';
        header('Location: /mi_tortuga/index.php?page=admin&from='.$fromPost.'&to='.$toPost);
        exit;
    }
    if (!in_array($estado, ['pendiente','enviado','entregado'], true)) {
        $estado = 'pendiente';
    }
    $stmt = $mysqli->prepare('UPDATE pedidos SET estado_envio=? WHERE id=?');
    $stmt->bind_param('si', $estado, $pedido_id);
    $stmt->execute();

    $stmt = $mysqli->prepare('INSERT INTO envios(pedido_id, tracking, estado) VALUES (?,?,?) ON DUPLICATE KEY UPDATE tracking=VALUES(tracking), estado=VALUES(estado)');
    $stmt->bind_param('iss', $pedido_id, $tracking, $estado);
    $stmt->execute();

    $_SESSION['admin_flash'] = 'Estado de envío actualizado correctamente.';
    header('Location: /mi_tortuga/index.php?page=admin&from='.$fromPost.'&to='.$toPost);
    exit;
}

$from = sanitize_date($_GET['from'] ?? date('Y-m-01'), date('Y-m-01'));
$to = sanitize_date($_GET['to'] ?? date('Y-m-d'), date('Y-m-d'));
$fromDate = $from . ' 00:00:00';
$toDate = $to . ' 23:59:59';

if ($action === 'export_csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="reporte_ventas_'.$from.'_'.$to.'.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Orden','Fecha','Cliente','Producto','Categoría','Cantidad','Precio Unitario','Subtotal','Método Pago','Método Envío','Estado Envío']);
    $stmt = $mysqli->prepare('SELECT p.numero_orden,p.creado_en,u.nombre AS cliente,prod.nombre AS producto,IFNULL(cat.nombre,"") AS categoria,pd.cantidad,pd.precio_unitario,(pd.cantidad*pd.precio_unitario) AS subtotal,p.metodo_pago,p.metodo_envio,p.estado_envio FROM pedidos p JOIN usuarios u ON u.id=p.usuario_id JOIN pedido_detalles pd ON pd.pedido_id=p.id JOIN productos prod ON prod.id=pd.producto_id LEFT JOIN categorias cat ON cat.id=prod.categoria_id WHERE p.creado_en BETWEEN ? AND ? ORDER BY p.creado_en');
    $stmt->bind_param('ss', $fromDate, $toDate);
    $stmt->execute();
    $rows = $stmt->get_result();
    while ($row = $rows->fetch_assoc()) {
        fputcsv($out, [
            $row['numero_orden'],
            $row['creado_en'],
            $row['cliente'],
            $row['producto'],
            $row['categoria'],
            $row['cantidad'],
            number_format($row['precio_unitario'],2,'.',''),
            number_format($row['subtotal'],2,'.',''),
            $row['metodo_pago'],
            $row['metodo_envio'],
            $row['estado_envio']
        ]);
    }
    fclose($out);
    exit;
}

if ($action === 'export_pdf') {
    require_once __DIR__ . '/../includes/fpdf.php';
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,10,utf8_decode('Reporte de ventas Mi Tortuga'),0,1,'C');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(0,6,utf8_decode('Rango: '.$from.' al '.$to),0,1,'C');
    $pdf->Ln(4);
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(30,8,utf8_decode('Fecha'),1);
    $pdf->Cell(35,8,utf8_decode('Orden'),1);
    $pdf->Cell(40,8,utf8_decode('Producto'),1);
    $pdf->Cell(35,8,utf8_decode('Categoría'),1);
    $pdf->Cell(20,8,utf8_decode('Cant.'),1,0,'R');
    $pdf->Cell(30,8,utf8_decode('Subtotal'),1,1,'R');
    $pdf->SetFont('Arial','',8);
    $stmt = $mysqli->prepare('SELECT p.numero_orden,p.creado_en,prod.nombre AS producto,IFNULL(cat.nombre,"") AS categoria,pd.cantidad,(pd.cantidad*pd.precio_unitario) AS subtotal FROM pedidos p JOIN pedido_detalles pd ON pd.pedido_id=p.id JOIN productos prod ON prod.id=pd.producto_id LEFT JOIN categorias cat ON cat.id=prod.categoria_id WHERE p.creado_en BETWEEN ? AND ? ORDER BY p.creado_en');
    $stmt->bind_param('ss', $fromDate, $toDate);
    $stmt->execute();
    $rows = $stmt->get_result();
    $totalGeneral = 0;
    while ($row = $rows->fetch_assoc()) {
        $pdf->Cell(30,7,utf8_decode(substr($row['creado_en'],0,10)),1);
        $pdf->Cell(35,7,utf8_decode($row['numero_orden']),1);
        $pdf->Cell(40,7,utf8_decode(mb_strimwidth($row['producto'],0,22,'...')),1);
        $pdf->Cell(35,7,utf8_decode(mb_strimwidth($row['categoria'],0,18,'...')),1);
        $pdf->Cell(20,7,$row['cantidad'],1,0,'R');
        $pdf->Cell(30,7,number_format($row['subtotal'],2),1,1,'R');
        $totalGeneral += $row['subtotal'];
    }
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(160,8,utf8_decode('Total general'),1);
    $pdf->Cell(30,8,utf8_decode('Q '.number_format($totalGeneral,2)),1,1,'R');
    $pdf->Output('D','reporte_ventas_'.$from.'_'.$to.'.pdf');
    exit;
}

http_response_code(400);
exit('Acción no soportada');
