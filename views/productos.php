<?php
// views/productos.php
require_once __DIR__ . '/../includes/conexion.php';

$cat = $_GET['cat'] ?? '';
$order = $_GET['order'] ?? 'popularidad';
$busca = $_GET['q'] ?? '';

$sql = "SELECT p.*, c.nombre AS categoria FROM productos p LEFT JOIN categorias c ON c.id=p.categoria_id WHERE 1";
$params = [];
if ($cat !== '') { $sql .= " AND c.nombre = ?"; $params[] = $cat; }
if ($busca !== '') { $sql .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)"; $params[]="%$busca%"; $params[]="%$busca%"; }
$orderBy = in_array($order, ['precio','popularidad','creado_en']) ? $order : 'popularidad';
$sql .= " ORDER BY $orderBy DESC";

$stmt = $mysqli->prepare($sql);
if (!empty($params)) {
  $types = str_repeat('s', count($params));
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
?>
<h2 class="mb-3">Catálogo de Productos</h2>
<form class="row g-2 mb-3">
  <div class="col-md-3"><input class="form-control" type="text" name="q" placeholder="Buscar..." value="<?php echo htmlspecialchars($busca); ?>"></div>
  <div class="col-md-3">
    <select name="cat" class="form-select">
      <option value="">Todas las categorías</option>
      <?php
      $cats = $mysqli->query("SELECT nombre FROM categorias ORDER BY nombre");
      while($r=$cats->fetch_assoc()) {
        $sel = ($r['nombre']===$cat)?'selected':'';
        echo '<option '.$sel.'>'.htmlspecialchars($r['nombre']).'</option>';
      }
      ?>
    </select>
  </div>
  <div class="col-md-3">
    <select name="order" class="form-select">
      <option value="popularidad" <?php echo $order==='popularidad'?'selected':''; ?>>Popularidad</option>
      <option value="precio" <?php echo $order==='precio'?'selected':''; ?>>Precio</option>
      <option value="creado_en" <?php echo $order==='creado_en'?'selected':''; ?>>Novedades</option>
    </select>
  </div>
  <div class="col-md-3 d-grid"><button class="btn btn-success"><i class="fa fa-filter me-1"></i> Filtrar</button></div>
</form>

<div class="row g-3">
<?php while($p=$res->fetch_assoc()): ?>
  <div class="col-md-4">
    <div class="card h-100 shadow-sm">
      <img src="/mi_tortuga/assets/images/<?php echo htmlspecialchars($p['imagen'] ?: 'placeholder.png'); ?>" class="card-img-top" alt="">
      <div class="card-body d-flex flex-column">
        <h5 class="card-title"><?php echo htmlspecialchars($p['nombre']); ?></h5>
        <p class="text-muted small mb-1"><?php echo htmlspecialchars($p['categoria'] ?? ''); ?></p>
        <p class="card-text flex-grow-1"><?php echo htmlspecialchars($p['descripcion']); ?></p>
        <div class="d-flex justify-content-between align-items-center">
          <strong>Q <?php echo number_format($p['precio'],2); ?></strong>
          <button class="btn btn-outline-success btn-sm" onclick="addToCart(<?php echo (int)$p['id']; ?>)">
            <i class="fa fa-cart-plus"></i> Agregar
          </button>
        </div>
      </div>
    </div>
  </div>
<?php endwhile; ?>
</div>
<hr class="my-4">
<h3>Opiniones recientes</h3>
<?php
$rev = $mysqli->query("SELECT r.*, u.nombre AS usuario, p.nombre AS producto 
  FROM resenas r JOIN usuarios u ON u.id=r.usuario_id JOIN productos p ON p.id=r.producto_id 
  ORDER BY r.creado_en DESC LIMIT 5");
while($r=$rev->fetch_assoc()){
  echo '<div class="mb-2"><strong>'.htmlspecialchars($r['usuario']).'</strong> sobre <em>'.htmlspecialchars($r['producto']).'</em>: ';
  echo str_repeat('⭐', (int)$r['calificacion']).' - '.htmlspecialchars($r['comentario']).'</div>';
}
?>
<script src="/mi_tortuga/assets/js/cart.js"></script>
