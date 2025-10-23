<?php
// views/carrito.php
require_once __DIR__ . '/../includes/conexion.php';
?>
<h2>Carrito</h2>
<?php if (!empty($_SESSION['auth_error'])): ?>
  <div class="alert alert-danger"><?php echo $_SESSION['auth_error']; unset($_SESSION['auth_error']); ?></div>
<?php elseif (!empty($_SESSION['auth_success'])): ?>
  <div class="alert alert-success"><?php echo $_SESSION['auth_success']; unset($_SESSION['auth_success']); ?></div>
<?php endif; ?>
<div id="cart-container" class="table-responsive"></div>
<div class="d-flex gap-2 mt-3">
  <a href="/mi_tortuga/index.php?page=productos" class="btn btn-outline-secondary">Seguir comprando</a>
  <a href="/mi_tortuga/index.php?page=pago" class="btn btn-success">Proceder al pago</a>
</div>
<script src="/mi_tortuga/assets/js/cart.js"></script>
<script>renderCartTable();</script>
