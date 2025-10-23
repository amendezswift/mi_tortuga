<?php // views/login.php ?>
<h2>Ingresar</h2>
<?php if (!empty($_SESSION['auth_error'])): ?>
  <div class="alert alert-danger"><?php echo $_SESSION['auth_error']; unset($_SESSION['auth_error']); ?></div>
<?php elseif (!empty($_SESSION['auth_success'])): ?>
  <div class="alert alert-success"><?php echo $_SESSION['auth_success']; unset($_SESSION['auth_success']); ?></div>
<?php endif; ?>
<form method="post" action="/mi_tortuga/controllers/auth.php?action=login" class="col-md-6">
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" class="form-control" name="email" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Contraseña</label>
    <input type="password" class="form-control" name="password" required>
  </div>
  <div class="mb-3 form-check">
    <input class="form-check-input" type="checkbox" name="accept_terms" required id="acc">
    <label class="form-check-label" for="acc">Acepto los <a href="/mi_tortuga/index.php?page=terminos" target="_blank">Términos</a> y la <a href="/mi_tortuga/index.php?page=privacidad" target="_blank">Política de Privacidad</a></label>
  </div>
  <button class="btn btn-success">Ingresar</button>
  <a class="btn btn-link" href="/mi_tortuga/index.php?page=registro">Crear cuenta</a>
  <a class="btn btn-link" href="/mi_tortuga/index.php?page=recuperar">¿Olvidaste tu contraseña?</a>
</form>
