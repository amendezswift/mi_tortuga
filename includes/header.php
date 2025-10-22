<?php
// includes/header.php
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mi Tortuga</title>
  <link rel="icon" href="/mi_tortuga/assets/images/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="/mi_tortuga/assets/css/styles.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="/mi_tortuga/index.php">
      <i class="fa-solid fa-turtle me-2"></i>Mi Tortuga
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="/mi_tortuga/index.php?page=productos">Productos</a></li>
        <li class="nav-item"><a class="nav-link" href="/mi_tortuga/index.php?page=carrito">Carrito</a></li>
        <li class="nav-item"><a class="nav-link" href="/mi_tortuga/index.php?page=faq">FAQ</a></li>
        <li class="nav-item"><a class="nav-link" href="/mi_tortuga/index.php?page=soporte">Soporte</a></li>
        <?php if (!empty($_SESSION['rol']) && $_SESSION['rol']==='admin'): ?>
          <li class="nav-item"><a class="nav-link" href="/mi_tortuga/index.php?page=admin">Admin</a></li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav">
        <?php if (!empty($_SESSION['usuario_id'])): ?>
          <li class="nav-item"><a class="nav-link" href="/mi_tortuga/index.php?page=perfil"><i class="fa fa-user"></i> Perfil</a></li>
          <li class="nav-item"><a class="nav-link" href="/mi_tortuga/controllers/auth.php?action=logout">Salir</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="/mi_tortuga/index.php?page=login">Ingresar</a></li>
          <li class="nav-item"><a class="nav-link" href="/mi_tortuga/index.php?page=registro">Registrarse</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<main class="container py-4">
