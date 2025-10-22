<?php
// controllers/auth.php
require_once __DIR__ . '/../includes/conexion.php';

$action = $_GET['action'] ?? '';

if ($action==='register') {
  $nombre = trim($_POST['nombre'] ?? '');
  $email = strtolower(trim($_POST['email'] ?? ''));
  $password = $_POST['password'] ?? '';
  if (!$nombre || !$email || !$password) { header('Location: /mi_tortuga/index.php?page=registro'); exit; }
  $hash = password_hash($password, PASSWORD_BCRYPT);
  $stmt = $mysqli->prepare("INSERT INTO usuarios(nombre,email,password_hash) VALUES (?,?,?)");
  $stmt->bind_param('sss', $nombre, $email, $hash);
  if ($stmt->execute()) {
    $_SESSION['usuario_id'] = $stmt->insert_id;
    $_SESSION['rol'] = 'cliente';
    header('Location: /mi_tortuga/index.php?page=perfil'); exit;
  } else {
    echo "Error: ".$mysqli->error;
  }
}
elseif ($action==='login') {
  $email = strtolower(trim($_POST['email'] ?? ''));
  $password = $_POST['password'] ?? '';
  $stmt = $mysqli->prepare("SELECT id,password_hash,rol FROM usuarios WHERE email=?");
  $stmt->bind_param('s',$email);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_assoc();
  if ($res && password_verify($password, $res['password_hash'])) {
    $_SESSION['usuario_id'] = (int)$res['id'];
    $_SESSION['rol'] = $res['rol'];
    header('Location: /mi_tortuga/index.php?page=perfil'); exit;
  } else {
    echo "<div class='container py-4'><div class='alert alert-danger'>Credenciales inválidas</div></div>";
  }
}
elseif ($action==='logout') {
  session_destroy(); header('Location: /mi_tortuga/index.php'); exit;
}
elseif ($action==='update_profile') {
  if (empty($_SESSION['usuario_id'])) { http_response_code(403); exit; }
  $uid = (int)$_SESSION['usuario_id'];
  $nombre = trim($_POST['nombre'] ?? '');
  $pass = $_POST['password'] ?? '';
  if ($pass) {
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $stmt = $mysqli->prepare("UPDATE usuarios SET nombre=?, password_hash=? WHERE id=?");
    $stmt->bind_param('ssi',$nombre,$hash,$uid);
  } else {
    $stmt = $mysqli->prepare("UPDATE usuarios SET nombre=? WHERE id=?");
    $stmt->bind_param('si',$nombre,$uid);
  }
  $stmt->execute();
  header('Location: /mi_tortuga/index.php?page=perfil'); exit;
}
