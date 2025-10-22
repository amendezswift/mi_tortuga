<?php // views/registro.php ?>
<h2>Registrarse</h2>
<form method="post" action="/mi_tortuga/controllers/auth.php?action=register" class="col-md-6">
  <div class="mb-3">
    <label class="form-label">Nombre</label>
    <input class="form-control" name="nombre" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" class="form-control" name="email" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Contraseña</label>
    <input type="password" class="form-control" name="password" required>
  </div>
  <div class="mb-3 form-check">
    <input class="form-check-input" type="checkbox" name="accept_terms" required id="acc2">
    <label class="form-check-label" for="acc2">Acepto los <a href="/mi_tortuga/index.php?page=terminos" target="_blank">Términos</a> y la <a href="/mi_tortuga/index.php?page=privacidad" target="_blank">Política de Privacidad</a></label>
  </div>
  <button class="btn btn-success">Crear cuenta</button>
</form>
