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
<?php if (!empty($_SESSION['auth_success'])): ?>
  <div class="alert alert-success"><?php echo $_SESSION['auth_success']; unset($_SESSION['auth_success']); ?></div>
<?php elseif (!empty($_SESSION['auth_error'])): ?>
  <div class="alert alert-danger"><?php echo $_SESSION['auth_error']; unset($_SESSION['auth_error']); ?></div>
<?php endif; ?>
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
      $pedidos = $mysqli->query("SELECT p.*, e.tracking, e.estado AS estado_guia FROM pedidos p LEFT JOIN envios e ON e.pedido_id=p.id WHERE p.usuario_id=$uid ORDER BY p.creado_en DESC LIMIT 20");
      if ($pedidos->num_rows === 0) {
        echo '<li class="list-group-item text-muted">Aún no tienes pedidos registrados.</li>';
      }
      while($p=$pedidos->fetch_assoc()){
        $estado = $p['estado_envio'];
        $pasos = ['pendiente','enviado','entregado'];
        $indice = array_search($estado, $pasos, true);
        $porcentaje = $indice === false ? 0 : (($indice) / (count($pasos)-1)) * 100;
        echo '<li class="list-group-item">';
        echo '<div class="d-flex justify-content-between"><strong>#'.htmlspecialchars($p['numero_orden']).'</strong><span>Q '.number_format($p['total'],2).'</span></div>';
        echo '<div class="small text-muted">Pedido realizado el '.htmlspecialchars($p['creado_en']).' • Envío: '.htmlspecialchars($p['metodo_envio']).' • Pago: '.htmlspecialchars($p['metodo_pago']).'</div>';
        echo '<div class="progress my-2" style="height:8px;"><div class="progress-bar bg-success" role="progressbar" style="width: '.$porcentaje.'%"></div></div>';
        echo '<div class="d-flex justify-content-between small"><span>Estado: '.htmlspecialchars($estado).'</span>';
        $tracking = $p['tracking'] ?: 'Asignado al despachar';
        echo '<span>Guía: '.htmlspecialchars($tracking).'</span></div>';
        if (!empty($p['estado_guia']) && $p['estado_guia'] !== $estado) {
          echo '<div class="text-muted small">Última actualización de paquetería: '.htmlspecialchars($p['estado_guia']).'</div>';
        }
        echo '</li>';
      }
      ?>
    </ul>
  </div>
</div>
