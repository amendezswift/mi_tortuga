<?php
// views/confirmacion.php
$orden = $_GET['orden'] ?? '';
?>
<div class="alert alert-success">
  <h2>¡Gracias por tu compra!</h2>
  <p>Tu número de orden es <strong><?php echo htmlspecialchars($orden); ?></strong>.</p>
</div>
