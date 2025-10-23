<?php
// views/recuperar.php
?>
<h2>Recuperar contraseña</h2>
<p>Ingresa tu correo y te enviaremos un enlace para restablecer tu contraseña.</p>
<form method="post" action="/mi_tortuga/controllers/auth.php?action=forgot" class="col-md-6">
  <?php if (!empty($_SESSION['auth_error'])): ?>
    <div class="alert alert-danger"><?php echo $_SESSION['auth_error']; unset($_SESSION['auth_error']); ?></div>
  <?php elseif (!empty($_SESSION['auth_success'])): ?>
    <div class="alert alert-success"><?php echo $_SESSION['auth_success']; unset($_SESSION['auth_success']); ?></div>
  <?php endif; ?>
  <div class="mb-3">
    <label class="form-label">Correo electrónico</label>
    <input type="email" class="form-control" name="email" required>
  </div>
  <button class="btn btn-success">Enviar enlace seguro</button>
</form>
