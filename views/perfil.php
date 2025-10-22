<?php
// views/perfil.php
require_once __DIR__ . '/../includes/conexion.php';
if (empty($_SESSION['usuario_id'])) {
  header('Location: /mi_tortuga/index.php?page=login'); exit;
}
$uid = (int)$_SESSION['usuario_id'];
$user = $mysqli->query("SELECT * FROM usuarios WHERE id=$uid")->fetch_assoc();
?>
<h2>Mi Perfil</h2>
<div class="row">
  <div class="col-md-6">
    <form method="post" action="/mi_tortuga/controllers/auth.php?action=update_profile">
      <div class="mb-3">
        <label class="form-label">Nombre</label>
        <input class="form-control" name="nombre" value="<?php echo htmlspecialchars($user['nombre']); ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
      </div>
      <div class="mb-3">
        <label class="form-label">Nueva contraseña (opcional)</label>
        <input class="form-control" type="password" name="password">
      </div>
      <button class="btn btn-success">Guardar cambios</button>
    </form>
  </div>
  <div class="col-md-6">
    <h4>Historial de pedidos</h4>
    <ul class="list-group">
      <?php
      $pedidos = $mysqli->query("SELECT * FROM pedidos WHERE usuario_id=$uid ORDER BY creado_en DESC LIMIT 20");
      while($p=$pedidos->fetch_assoc()){
        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
        echo '<span>#'.$p['numero_orden'].' • '.htmlspecialchars($p['metodo_envio']).' • '.$p['estado_envio'].'</span>';
        echo '<span>Q '.number_format($p['total'],2).'</span>';
        echo '</li>';
      }
      ?>
    </ul>
  </div>
</div>
