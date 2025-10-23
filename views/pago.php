<?php
// views/pago.php
require_once __DIR__ . '/../includes/conexion.php';
if (empty($_SESSION['usuario_id'])) { header('Location: /mi_tortuga/index.php?page=login'); exit; }
?>
<h2>Pago Seguro</h2>
<?php if (!empty($_SESSION['auth_error'])): ?>
  <div class="alert alert-danger"><?php echo $_SESSION['auth_error']; unset($_SESSION['auth_error']); ?></div>
<?php elseif (!empty($_SESSION['auth_success'])): ?>
  <div class="alert alert-success"><?php echo $_SESSION['auth_success']; unset($_SESSION['auth_success']); ?></div>
<?php endif; ?>
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
  <div class="col-12">
    <div class="bg-white border rounded p-3" id="checkout-summary">
      <div class="text-muted">Calculando resumen...</div>
    </div>
  </div>
  <div class="col-12 form-check">
    <input class="form-check-input" type="checkbox" name="accept_terms" id="checkout_terms" required>
    <label class="form-check-label" for="checkout_terms">Acepto los <a href="/mi_tortuga/index.php?page=terminos" target="_blank">Términos y Condiciones</a>, la <a href="/mi_tortuga/index.php?page=privacidad" target="_blank">Política de Privacidad</a> y confirmo que la transacción se realiza mediante canales seguros <code>https://</code>.</label>
  </div>
  <div class="col-12 d-grid">
    <button class="btn btn-success btn-lg">Pagar con SSL (simulado)</button>
  </div>
</form>
<script>
async function updateCheckoutSummary(){
  const box = document.getElementById('checkout-summary');
  const submitBtn = document.querySelector('form button.btn-success');
  try {
    const res = await fetch('/mi_tortuga/controllers/cart.php?action=list');
    const data = await res.json();
    if (!data.ok || !data.items || data.items.length === 0) {
      box.innerHTML = '<div class="alert alert-warning mb-0">Tu carrito está vacío.</div>';
      if (submitBtn) submitBtn.disabled = true;
      return;
    }
    if (submitBtn) submitBtn.disabled = false;
    const metodoEnvio = document.querySelector('select[name="metodo_envio"]').value;
    const subtotal = Number(data.subtotal || 0);
    const iva = Math.round(subtotal * 0.12 * 100) / 100;
    let envio = 0;
    if (metodoEnvio === 'expres') envio = 45;
    else if (metodoEnvio === 'gratuito') envio = 0;
    else envio = subtotal >= 300 ? 0 : 25;
    const total = subtotal + iva + envio;
    box.innerHTML = `
      <h5 class="mb-3">Resumen del pedido</h5>
      <ul class="list-unstyled mb-3">
        ${data.items.map(it => `<li>${it.cantidad} × ${it.nombre} <span class="float-end">Q ${Number(it.subtotal).toFixed(2)}</span></li>`).join('')}
      </ul>
      <div class="d-flex justify-content-between"><span>Subtotal</span><strong>Q ${subtotal.toFixed(2)}</strong></div>
      <div class="d-flex justify-content-between"><span>IVA (12%)</span><strong>Q ${iva.toFixed(2)}</strong></div>
      <div class="d-flex justify-content-between"><span>Envío (${metodoEnvio})</span><strong>Q ${envio.toFixed(2)}</strong></div>
      <div class="d-flex justify-content-between fs-5 border-top pt-2"><span>Total</span><strong>Q ${total.toFixed(2)}</strong></div>
    `;
  } catch (e) {
    box.innerHTML = '<div class="alert alert-danger mb-0">No se pudo cargar el resumen. Intenta de nuevo.</div>';
    if (submitBtn) submitBtn.disabled = true;
  }
}
document.querySelector('select[name="metodo_envio"]').addEventListener('change', updateCheckoutSummary);
updateCheckoutSummary();
</script>
