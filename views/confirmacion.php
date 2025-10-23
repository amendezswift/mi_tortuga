<?php
// views/confirmacion.php
$orden = $_GET['orden'] ?? '';
?>
<div class="alert alert-success">
  <h2>¡Gracias por tu compra!</h2>
  <p>Tu número de orden es <strong><?php echo htmlspecialchars($orden); ?></strong>.</p>
  <p>Te enviamos un correo con los detalles del pedido. Puedes seguir el estado del envío y descargar tu factura desde la sección <a href="/mi_tortuga/index.php?page=perfil">Mi Perfil</a>.</p>
</div>
