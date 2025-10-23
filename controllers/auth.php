<?php
// controllers/auth.php
require_once __DIR__ . '/../includes/conexion.php';

$action = $_GET['action'] ?? '';

if ($action === 'register') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    if (empty($_POST['accept_terms'])) {
        $_SESSION['auth_error'] = 'Debes aceptar los términos y la política de privacidad.';
        header('Location: /mi_tortuga/index.php?page=registro');
        exit;
    }
    if (!$nombre || !$email || !$password) {
        $_SESSION['auth_error'] = 'Completa todos los campos del formulario de registro.';
        header('Location: /mi_tortuga/index.php?page=registro');
        exit;
    }
    $stmt = $mysqli->prepare("SELECT id FROM usuarios WHERE email=?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()) {
        $_SESSION['auth_error'] = 'El correo ya está registrado. Intenta iniciar sesión.';
        header('Location: /mi_tortuga/index.php?page=login');
        exit;
    }
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $mysqli->prepare("INSERT INTO usuarios(nombre,email,password_hash) VALUES (?,?,?)");
    $stmt->bind_param('sss', $nombre, $email, $hash);
    if ($stmt->execute()) {
        $_SESSION['usuario_id'] = $stmt->insert_id;
        $_SESSION['rol'] = 'cliente';
        $_SESSION['auth_success'] = '¡Registro exitoso! Bienvenido a Mi Tortuga.';
        header('Location: /mi_tortuga/index.php?page=perfil');
        exit;
    }
    $_SESSION['auth_error'] = 'No fue posible crear la cuenta. Intenta más tarde.';
    header('Location: /mi_tortuga/index.php?page=registro');
    exit;
}

if ($action === 'login') {
    if (empty($_POST['accept_terms'])) {
        $_SESSION['auth_error'] = 'Debes aceptar los términos para continuar.';
        header('Location: /mi_tortuga/index.php?page=login');
        exit;
    }
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $stmt = $mysqli->prepare("SELECT id,password_hash,rol FROM usuarios WHERE email=?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res && password_verify($password, $res['password_hash'])) {
        $_SESSION['usuario_id'] = (int)$res['id'];
        $_SESSION['rol'] = $res['rol'];
        $_SESSION['auth_success'] = 'Sesión iniciada correctamente.';
        header('Location: /mi_tortuga/index.php?page=perfil');
        exit;
    }
    $_SESSION['auth_error'] = 'Credenciales inválidas. Verifica tu correo y contraseña.';
    header('Location: /mi_tortuga/index.php?page=login');
    exit;
}

if ($action === 'logout') {
    session_destroy();
    header('Location: /mi_tortuga/index.php');
    exit;
}

if ($action === 'update_profile') {
    if (empty($_SESSION['usuario_id'])) {
        http_response_code(403);
        exit;
    }
    $uid = (int)$_SESSION['usuario_id'];
    $nombre = trim($_POST['nombre'] ?? '');
    $pass = $_POST['password'] ?? '';
    if ($pass) {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $stmt = $mysqli->prepare("UPDATE usuarios SET nombre=?, password_hash=? WHERE id=?");
        $stmt->bind_param('ssi', $nombre, $hash, $uid);
    } else {
        $stmt = $mysqli->prepare("UPDATE usuarios SET nombre=? WHERE id=?");
        $stmt->bind_param('si', $nombre, $uid);
    }
    $stmt->execute();
    $_SESSION['auth_success'] = 'Perfil actualizado correctamente.';
    header('Location: /mi_tortuga/index.php?page=perfil');
    exit;
}

if ($action === 'forgot') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (!$email) {
        $_SESSION['auth_error'] = 'Ingresa un correo válido.';
        header('Location: /mi_tortuga/index.php?page=recuperar');
        exit;
    }
    $stmt = $mysqli->prepare("SELECT id FROM usuarios WHERE email=?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user) {
        $tokenPlain = bin2hex(random_bytes(16));
        $tokenHash = password_hash($tokenPlain, PASSWORD_BCRYPT);
        $expires = date('Y-m-d H:i:s', time() + 3600);
        $stmt = $mysqli->prepare("INSERT INTO password_resets(usuario_id, token_hash, expira_en) VALUES (?,?,?) ON DUPLICATE KEY UPDATE token_hash=VALUES(token_hash), expira_en=VALUES(expira_en)");
        $stmt->bind_param('iss', $user['id'], $tokenHash, $expires);
        $stmt->execute();
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $link = 'https://' . $host . '/mi_tortuga/index.php?page=restablecer&token=' . $tokenPlain . '&email=' . urlencode($email);
        $_SESSION['auth_success'] = 'Hemos enviado un enlace seguro a tu correo. En modo local puedes usar este enlace directo: <a href="' . htmlspecialchars($link, ENT_QUOTES) . '">Restablecer contraseña</a>.';
    } else {
        $_SESSION['auth_success'] = 'Si el correo existe en el sistema, recibirás instrucciones para restablecer la contraseña.';
    }
    header('Location: /mi_tortuga/index.php?page=recuperar');
    exit;
}

if ($action === 'reset') {
    $token = $_POST['token'] ?? '';
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    if (!$token || !$email || !$password) {
        $_SESSION['auth_error'] = 'Solicitud inválida.';
        header('Location: /mi_tortuga/index.php?page=recuperar');
        exit;
    }
    if ($password !== $password_confirm) {
        $_SESSION['auth_error'] = 'Las contraseñas no coinciden.';
        header('Location: /mi_tortuga/index.php?page=restablecer&token=' . urlencode($token) . '&email=' . urlencode($email));
        exit;
    }
    $stmt = $mysqli->prepare("SELECT id FROM usuarios WHERE email=?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if (!$user) {
        $_SESSION['auth_error'] = 'Usuario no encontrado.';
        header('Location: /mi_tortuga/index.php?page=recuperar');
        exit;
    }
    $stmt = $mysqli->prepare("SELECT token_hash, expira_en FROM password_resets WHERE usuario_id=?");
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    $reset = $stmt->get_result()->fetch_assoc();
    if (!$reset || strtotime($reset['expira_en']) < time() || !password_verify($token, $reset['token_hash'])) {
        $_SESSION['auth_error'] = 'Enlace caducado o inválido. Solicita uno nuevo.';
        header('Location: /mi_tortuga/index.php?page=recuperar');
        exit;
    }
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $mysqli->prepare("UPDATE usuarios SET password_hash=? WHERE id=?");
    $stmt->bind_param('si', $hash, $user['id']);
    $stmt->execute();
    $stmt = $mysqli->prepare("DELETE FROM password_resets WHERE usuario_id=?");
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    $_SESSION['auth_success'] = 'Contraseña actualizada. Ya puedes iniciar sesión.';
    header('Location: /mi_tortuga/index.php?page=login');
    exit;
}

http_response_code(400);
print 'Acción no soportada';
