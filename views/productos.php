<?php
// views/productos.php
require_once __DIR__ . '/../includes/conexion.php';

$cat = $_GET['cat'] ?? '';
$order = $_GET['order'] ?? 'popularidad';
$busca = $_GET['q'] ?? '';

$sql = "SELECT p.*, c.nombre AS categoria, ROUND(IFNULL(AVG(r.calificacion),0),1) AS rating_prom, COUNT(r.id) AS rating_total"
     . " FROM productos p"
     . " LEFT JOIN categorias c ON c.id=p.categoria_id"
     . " LEFT JOIN resenas r ON r.producto_id = p.id"
     . " WHERE 1";
$params = [];
if ($cat !== '') { $sql .= " AND c.nombre = ?"; $params[] = $cat; }
if ($busca !== '') { $sql .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)"; $params[]="%$busca%"; $params[]="%$busca%"; }
$orderBy = in_array($order, ['precio','popularidad','creado_en']) ? $order : 'popularidad';
$sql .= " GROUP BY p.id ORDER BY $orderBy DESC";

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
  <div class="col-md-3 d-grid"><button class="btn btn-success"><i class="fa-solid fa-filter me-1"></i> Filtrar</button></div>
</form>

<div class="row g-3">
<?php if ($res->num_rows === 0): ?>
  <div class="col-12">
    <div class="alert alert-info">No se encontraron productos para los filtros seleccionados.</div>
  </div>
<?php endif; ?>
<?php while($p=$res->fetch_assoc()): ?>
  <div class="col-md-4">
    <div class="card h-100 shadow-sm">
      <img src="/mi_tortuga/assets/images/<?php echo htmlspecialchars($p['imagen'] ?: 'placeholder.png'); ?>" class="card-img-top" alt="">
      <div class="card-body d-flex flex-column">
        <h5 class="card-title"><?php echo htmlspecialchars($p['nombre']); ?></h5>
        <p class="text-muted small mb-1"><?php echo htmlspecialchars($p['categoria'] ?? ''); ?></p>
        <p class="card-text flex-grow-1"><?php echo htmlspecialchars($p['descripcion']); ?></p>
        <div class="mb-2 small text-warning">
          <?php
          $filled = (int)round($p['rating_prom']);
          for ($i=1;$i<=5;$i++) {
            echo $i <= $filled ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
          }
          ?>
          <span class="text-muted ms-1"><?php echo number_format($p['rating_prom'],1); ?> (<?php echo (int)$p['rating_total']; ?>)</span>
        </div>
        <p class="mb-2"><span class="badge <?php echo $p['stock'] > 5 ? 'bg-success' : ($p['stock']>0?'bg-warning text-dark':'bg-danger'); ?>"><?php echo $p['stock']>0 ? 'Disponible: '.$p['stock'] : 'Agotado'; ?></span></p>
        <div class="d-flex justify-content-between align-items-center">
          <strong>Q <?php echo number_format($p['precio'],2); ?></strong>
          <div class="btn-group">
            <button class="btn btn-outline-success btn-sm" onclick="addToCart(<?php echo (int)$p['id']; ?>)" <?php echo $p['stock']>0?'':'disabled'; ?>>
              <i class="fa-solid fa-cart-plus"></i>
            </button>
            <?php if (!empty($_SESSION['usuario_id'])): ?>
              <button class="btn btn-outline-primary btn-sm" type="button" onclick="openReviewModal(<?php echo (int)$p['id']; ?>, '<?php echo htmlspecialchars($p['nombre'], ENT_QUOTES); ?>')">
                <i class="fa-solid fa-pen"></i>
              </button>
            <?php endif; ?>
          </div>
        </div>
        <?php if (empty($_SESSION['usuario_id'])): ?>
          <p class="mt-2 small text-muted">Inicia sesión para dejar tu reseña.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endwhile; ?>
</div>
<hr class="my-4">
<h3>Opiniones recientes</h3>
<?php
$rev = $mysqli->query("SELECT r.*, u.nombre AS usuario, p.nombre AS producto FROM resenas r JOIN usuarios u ON u.id=r.usuario_id JOIN productos p ON p.id=r.producto_id ORDER BY r.creado_en DESC LIMIT 5");
if ($rev) {
  if ($rev->num_rows === 0) {
    echo '<p class="text-muted">Aún no hay reseñas registradas.</p>';
  }
  while($r=$rev->fetch_assoc()){
    echo '<div class="mb-2"><strong>'.htmlspecialchars($r['usuario']).'</strong> sobre <em>'.htmlspecialchars($r['producto']).'</em>: ';
    echo str_repeat('⭐', (int)$r['calificacion']).' - '.htmlspecialchars($r['comentario']).'</div>';
  }
}
?>
<?php if (!empty($_SESSION['usuario_id'])): ?>
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Opinar sobre <span id="reviewProductoLabel"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form id="reviewForm" class="modal-body">
        <div id="reviewAlert" class="alert d-none" role="alert"></div>
        <input type="hidden" name="producto_id" id="reviewProducto">
        <div class="mb-3">
          <label class="form-label">Calificación</label>
          <select name="calificacion" class="form-select" required>
            <option value="5">5 - Excelente</option>
            <option value="4">4 - Muy bueno</option>
            <option value="3">3 - Bueno</option>
            <option value="2">2 - Regular</option>
            <option value="1">1 - Deficiente</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Comentario</label>
          <textarea name="comentario" class="form-control" rows="3" placeholder="Comparte tu experiencia"></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button class="btn btn-success">Guardar reseña</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
<script src="/mi_tortuga/assets/js/cart.js"></script>
<?php if (!empty($_SESSION['usuario_id'])): ?>
<script src="/mi_tortuga/assets/js/reviews.js"></script>
<?php endif; ?>
