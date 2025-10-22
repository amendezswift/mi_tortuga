<?php
// views/admin.php
require_once __DIR__ . '/../includes/conexion.php';
if (empty($_SESSION['rol']) || $_SESSION['rol']!=='admin') { http_response_code(403); echo "<div class='alert alert-danger'>Acceso denegado.</div>"; return; }
?>
<h2>Panel Administrativo</h2>
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
      echo '<tr>';
      echo '<td>'.$p['id'].'</td><td>'.htmlspecialchars($p['nombre']).'</td><td>Q '.number_format($p['precio'],2).'</td><td>'.$p['stock'].'</td>';
      echo '<td>
        <form class="d-inline" method="post" action="/mi_tortuga/controllers/productos.php?action=delete"><input type="hidden" name="id" value="'.$p['id'].'"><button class="btn btn-sm btn-outline-danger" onclick="return confirm(\'¿Eliminar?\')">Eliminar</button></form>
        <button class="btn btn-sm btn-outline-primary" onclick="prefillProd('.$p['id'].', \''.htmlspecialchars($p['nombre'],ENT_QUOTES).'\', '.$p['precio'].', '.$p['stock'].')">Editar</button>
      </td>';
      echo '</tr>';
    }
    ?>
  </tbody>
</table>

<form class="row g-2" method="post" action="/mi_tortuga/controllers/productos.php?action=update">
  <h5>Editar Producto</h5>
  <div class="col-md-2"><input class="form-control" name="id" id="e_id" placeholder="ID" readonly></div>
  <div class="col-md-3"><input class="form-control" name="nombre" id="e_nombre" placeholder="Nombre"></div>
  <div class="col-md-2"><input class="form-control" name="precio" id="e_precio" placeholder="Precio" type="number" step="0.01"></div>
  <div class="col-md-2"><input class="form-control" name="stock" id="e_stock" placeholder="Stock" type="number"></div>
  <div class="col-md-3 d-grid"><button class="btn btn-primary">Actualizar</button></div>
</form>

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
