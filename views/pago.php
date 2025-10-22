<?php
// views/pago.php
require_once __DIR__ . '/../includes/conexion.php';
if (empty($_SESSION['usuario_id'])) { header('Location: /mi_tortuga/index.php?page=login'); exit; }
?>
<h2>Pago Seguro</h2>
<div class="alert alert-info">
  Este entorno simula pagos. Todas las rutas de pago usan <strong>https://</strong> para ilustrar SSL. 
  <a href="/mi_tortuga/index.php?page=faq" target="_blank">Ver guía SSL</a>
</div>
<form method="post" action="/mi_tortuga/controllers/checkout.php?action=procesar" class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Método de envío</label>
    <select class="form-select" name="metodo_envio" required>
      <option value="normal">Normal</option>
      <option value="expres">Exprés</option>
      <option value="gratuito">Gratuito</option>
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label">Método de pago</label>
    <select class="form-select" name="metodo_pago" required>
      <option value="tarjeta">Tarjeta (simulado)</option>
      <option value="paypal">PayPal (simulado)</option>
      <option value="transferencia">Transferencia (simulado)</option>
    </select>
  </div>
  <div class="col-12 d-grid">
    <button class="btn btn-success btn-lg">Pagar con SSL (simulado)</button>
  </div>
</form>
