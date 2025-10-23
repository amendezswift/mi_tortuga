<?php
// views/admin.php
require_once __DIR__ . '/../includes/conexion.php';
if (empty($_SESSION['rol']) || $_SESSION['rol']!=='admin') { http_response_code(403); echo "<div class='alert alert-danger'>Acceso denegado.</div>"; return; }
$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');
?>
<h2>Panel Administrativo</h2>
<?php if (!empty($_SESSION['admin_flash'])): ?>
  <div class="alert alert-info"><?php echo $_SESSION['admin_flash']; unset($_SESSION['admin_flash']); ?></div>
<?php endif; ?>
<div class="row g-3">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">Ventas (últimos 30 días)</div>
      <div class="card-body"><canvas id="ventasChart"></canvas></div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">Top Productos</div>
      <div class="card-body"><canvas id="topChart"></canvas></div>
    </div>
  </div>
</div>

<hr>
<h3>CRUD de Productos</h3>
<form class="row g-2 mb-3" method="post" action="/mi_tortuga/controllers/productos.php?action=create" enctype="multipart/form-data">
  <div class="col-md-3"><input class="form-control" name="nombre" placeholder="Nombre" required></div>
  <div class="col-md-2"><input class="form-control" name="precio" placeholder="Precio" type="number" step="0.01" required></div>
  <div class="col-md-2"><input class="form-control" name="stock" placeholder="Stock" type="number" required></div>
  <div class="col-md-3"><input class="form-control" name="imagen" placeholder="Nombre de imagen (sube a assets/images)"></div>
  <div class="col-md-2 d-grid"><button class="btn btn-success">Crear</button></div>
  <div class="col-12"><input class="form-control" name="descripcion" placeholder="Descripción"></div>
</form>

<table class="table table-bordered">
  <thead><tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th><th>Acciones</th></tr></thead>
  <tbody>
    <?php
    $res = $mysqli->query("SELECT id,nombre,precio,stock FROM productos ORDER BY id DESC");
    while($p=$res->fetch_assoc()){
      $rowClass = $p['stock'] < 5 ? 'table-danger' : '';
      $nombreSafe = htmlspecialchars($p['nombre'], ENT_QUOTES);
      echo '<tr class="'.$rowClass.'">';
      echo '<td>'.$p['id'].'</td><td>'.htmlspecialchars($p['nombre']).'</td><td>Q '.number_format($p['precio'],2).'</td><td>'.$p['stock'].'</td>';
      echo '<td>
        <form class="d-inline" method="post" action="/mi_tortuga/controllers/productos.php?action=delete" onsubmit="return confirm(\'¿Eliminar producto?\')">
          <input type="hidden" name="id" value="'.$p['id'].'">
          <button class="btn btn-sm btn-outline-danger">Eliminar</button>
        </form>
        <button class="btn btn-sm btn-outline-primary" onclick="prefillProd('.$p['id'].', \''.$nombreSafe.'\', '.$p['precio'].', '.$p['stock'].')">Editar</button>
      </td>';
      echo '</tr>';
    }
    ?>
  </tbody>
</table>
<p class="small text-muted">Las filas en rojo indican inventario bajo (menos de 5 unidades).</p>

<form class="row g-2" method="post" action="/mi_tortuga/controllers/productos.php?action=update">
  <h5>Editar Producto</h5>
  <div class="col-md-2"><input class="form-control" name="id" id="e_id" placeholder="ID" readonly></div>
  <div class="col-md-3"><input class="form-control" name="nombre" id="e_nombre" placeholder="Nombre"></div>
  <div class="col-md-2"><input class="form-control" name="precio" id="e_precio" placeholder="Precio" type="number" step="0.01"></div>
  <div class="col-md-2"><input class="form-control" name="stock" id="e_stock" placeholder="Stock" type="number"></div>
  <div class="col-md-3 d-grid"><button class="btn btn-primary">Actualizar</button></div>
</form>

<hr class="mt-5">
<h3>Reportes y Envíos</h3>
<form class="row g-2 align-items-end mb-3" method="get" action="/mi_tortuga/index.php">
  <input type="hidden" name="page" value="admin">
  <div class="col-md-3">
    <label class="form-label">Desde</label>
    <input type="date" class="form-control" name="from" value="<?php echo htmlspecialchars($from, ENT_QUOTES); ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Hasta</label>
    <input type="date" class="form-control" name="to" value="<?php echo htmlspecialchars($to, ENT_QUOTES); ?>">
  </div>
  <div class="col-md-3 d-grid">
    <button class="btn btn-outline-primary">Filtrar</button>
  </div>
  <div class="col-md-3 d-grid gap-2">
    <a class="btn btn-outline-success" href="/mi_tortuga/controllers/pedidos.php?action=export_csv&amp;from=<?php echo urlencode($from); ?>&amp;to=<?php echo urlencode($to); ?>"><i class="fa-solid fa-file-excel me-1"></i> Exportar Excel (CSV)</a>
    <a class="btn btn-outline-danger" href="/mi_tortuga/controllers/pedidos.php?action=export_pdf&amp;from=<?php echo urlencode($from); ?>&amp;to=<?php echo urlencode($to); ?>"><i class="fa-solid fa-file-pdf me-1"></i> Exportar PDF</a>
  </div>
</form>

<table class="table table-striped">
  <thead>
    <tr>
      <th>Orden</th>
      <th>Fecha</th>
      <th>Total</th>
      <th>Método</th>
      <th>Estado envío</th>
      <th>Tracking</th>
      <th>Actualizar</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $fromDate = $from.' 00:00:00';
    $toDate = $to.' 23:59:59';
    $stmt = $mysqli->prepare("SELECT p.id,p.numero_orden,p.total,p.metodo_envio,p.estado_envio,p.creado_en,IFNULL(e.tracking,'') AS tracking, IFNULL(e.estado,'pendiente') AS estado_guia FROM pedidos p LEFT JOIN envios e ON e.pedido_id=p.id WHERE p.creado_en BETWEEN ? AND ? ORDER BY p.creado_en DESC LIMIT 50");
    $stmt->bind_param('ss', $fromDate, $toDate);
    $stmt->execute();
    $orders = $stmt->get_result();
    if ($orders->num_rows === 0) {
      echo '<tr><td colspan="7" class="text-center text-muted">Sin pedidos en el rango seleccionado.</td></tr>';
    }
    while($o=$orders->fetch_assoc()){
      echo '<tr>';
      echo '<td>#'.htmlspecialchars($o['numero_orden']).'</td>';
      echo '<td>'.htmlspecialchars($o['creado_en']).'</td>';
      echo '<td>Q '.number_format($o['total'],2).'</td>';
      echo '<td>'.htmlspecialchars($o['metodo_envio']).'</td>';
      echo '<td>'.htmlspecialchars($o['estado_envio']).'</td>';
      echo '<td>'.htmlspecialchars($o['tracking']).'</td>';
      echo '<td>
        <form class="row g-1 align-items-center" method="post" action="/mi_tortuga/controllers/pedidos.php?action=update_envio">
          <input type="hidden" name="pedido_id" value="'.$o['id'].'">
          <input type="hidden" name="from" value="'.htmlspecialchars($from, ENT_QUOTES).'">
          <input type="hidden" name="to" value="'.htmlspecialchars($to, ENT_QUOTES).'">
          <div class="col-md-4"><input class="form-control form-control-sm" name="tracking" value="'.htmlspecialchars($o['tracking']).'" placeholder="Guía"></div>
          <div class="col-md-4">
            <select class="form-select form-select-sm" name="estado_envio">
              <option value="pendiente"'.($o['estado_envio']==='pendiente'?' selected':'').'>Pendiente</option>
              <option value="enviado"'.($o['estado_envio']==='enviado'?' selected':'').'>Enviado</option>
              <option value="entregado"'.($o['estado_envio']==='entregado'?' selected':'').'>Entregado</option>
            </select>
          </div>
          <div class="col-md-4 d-grid"><button class="btn btn-sm btn-success">Guardar</button></div>
        </form>
      </td>';
      echo '</tr>';
    }
    ?>
  </tbody>
</table>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="/mi_tortuga/assets/js/charts.js"></script>
<script>
function prefillProd(id,nombre,precio,stock){
  document.getElementById('e_id').value=id;
  document.getElementById('e_nombre').value=nombre;
  document.getElementById('e_precio').value=precio;
  document.getElementById('e_stock').value=stock;
}
</script>
