<?php
// views/restablecer.php
$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';
?>
<h2>Restablecer contraseña</h2>
<form method="post" action="/mi_tortuga/controllers/auth.php?action=reset" class="col-md-6">
  <?php if (!empty($_SESSION['auth_error'])): ?>
    <div class="alert alert-danger"><?php echo $_SESSION['auth_error']; unset($_SESSION['auth_error']); ?></div>
  <?php elseif (!empty($_SESSION['auth_success'])): ?>
    <div class="alert alert-success"><?php echo $_SESSION['auth_success']; unset($_SESSION['auth_success']); ?></div>
  <?php endif; ?>
  <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
  <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
  <div class="mb-3">
    <label class="form-label">Nueva contraseña</label>
    <input type="password" class="form-control" name="password" required minlength="6">
  </div>
  <div class="mb-3">
    <label class="form-label">Confirmar contraseña</label>
    <input type="password" class="form-control" name="password_confirm" required minlength="6">
  </div>
  <button class="btn btn-success">Actualizar contraseña</button>
</form>
