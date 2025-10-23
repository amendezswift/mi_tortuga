<?php
// controllers/productos.php
require_once __DIR__ . '/../includes/conexion.php';
if (empty($_SESSION['rol']) || $_SESSION['rol']!=='admin') { http_response_code(403); die('Acceso denegado'); }

$action = $_GET['action'] ?? '';
if ($action==='create') {
  $nombre = $_POST['nombre'] ?? '';
  $precio = (float)($_POST['precio'] ?? 0);
  $stock = (int)($_POST['stock'] ?? 0);
  $imagen = $_POST['imagen'] ?? null;
  $descripcion = $_POST['descripcion'] ?? null;
  $stmt = $mysqli->prepare("INSERT INTO productos(nombre,precio,stock,imagen,descripcion) VALUES (?,?,?,?,?)");
  $stmt->bind_param('sdiis',$nombre,$precio,$stock,$imagen,$descripcion);
  $stmt->execute();
  $_SESSION['admin_flash'] = 'Producto creado correctamente.';
  header('Location: /mi_tortuga/index.php?page=admin'); exit;
}
elseif ($action==='update') {
  $id = (int)($_POST['id'] ?? 0);
  $nombre = $_POST['nombre'] ?? '';
  $precio = (float)($_POST['precio'] ?? 0);
  $stock = (int)($_POST['stock'] ?? 0);
  $stmt = $mysqli->prepare("UPDATE productos SET nombre=?, precio=?, stock=? WHERE id=?");
  $stmt->bind_param('sdii',$nombre,$precio,$stock,$id);
  $stmt->execute();
  $_SESSION['admin_flash'] = 'Producto actualizado.';
  header('Location: /mi_tortuga/index.php?page=admin'); exit;
}
elseif ($action==='delete') {
  $id = (int)($_POST['id'] ?? 0);
  $mysqli->query("DELETE FROM productos WHERE id=$id");
  $_SESSION['admin_flash'] = 'Producto eliminado.';
  header('Location: /mi_tortuga/index.php?page=admin'); exit;
}
