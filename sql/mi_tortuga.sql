
-- mi_tortuga.sql
-- Crear base de datos y usuario (ejecutar en mysql CLI)
-- mysql> CREATE DATABASE mi_tortuga CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- mysql> CREATE USER 'tortuga_user'@'localhost' IDENTIFIED BY 'tortuga_pass';
-- mysql> GRANT ALL PRIVILEGES ON mi_tortuga.* TO 'tortuga_user'@'localhost';
-- mysql> FLUSH PRIVILEGES;

USE mi_tortuga;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  rol ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tokens para recuperación de contraseña
CREATE TABLE IF NOT EXISTS password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL UNIQUE,
  token_hash VARCHAR(255) NOT NULL,
  expira_en DATETIME NOT NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabla de categorías
CREATE TABLE IF NOT EXISTS categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Productos
CREATE TABLE IF NOT EXISTS productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  categoria_id INT NULL,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT,
  precio DECIMAL(10,2) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  imagen VARCHAR(255),
  popularidad INT NOT NULL DEFAULT 0,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Reseñas
CREATE TABLE IF NOT EXISTS resenas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  producto_id INT NOT NULL,
  usuario_id INT NOT NULL,
  calificacion TINYINT NOT NULL CHECK (calificacion BETWEEN 1 AND 5),
  comentario TEXT,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  UNIQUE KEY unique_review (producto_id, usuario_id)
) ENGINE=InnoDB;

-- Carrito (persistencia por usuario o sesión)
CREATE TABLE IF NOT EXISTS carritos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NULL,
  session_id VARCHAR(128) NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_user (usuario_id),
  UNIQUE KEY unique_session (session_id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS carrito_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  carrito_id INT NOT NULL,
  producto_id INT NOT NULL,
  cantidad INT NOT NULL CHECK (cantidad > 0),
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_item (carrito_id, producto_id),
  FOREIGN KEY (carrito_id) REFERENCES carritos(id) ON DELETE CASCADE,
  FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB;

-- Pedidos
CREATE TABLE IF NOT EXISTS pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  impuestos DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  envio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  metodo_envio ENUM('normal','expres','gratuito') NOT NULL DEFAULT 'normal',
  metodo_pago ENUM('tarjeta','paypal','transferencia') NOT NULL,
  estado_pago ENUM('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
  estado_envio ENUM('pendiente','enviado','entregado') NOT NULL DEFAULT 'pendiente',
  numero_orden VARCHAR(32) NOT NULL UNIQUE,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pedido_detalles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT NOT NULL,
  producto_id INT NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  cantidad INT NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB;

-- Inventario (log de movimientos)
CREATE TABLE IF NOT EXISTS inventario_movs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  producto_id INT NOT NULL,
  cambio INT NOT NULL, -- negativo para ventas
  motivo VARCHAR(255) NOT NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB;

-- Envíos (tracking simple)
CREATE TABLE IF NOT EXISTS envios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT NOT NULL,
  tracking VARCHAR(64) NOT NULL,
  estado ENUM('pendiente','enviado','entregado') NOT NULL DEFAULT 'pendiente',
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  UNIQUE KEY unique_envio (pedido_id)
) ENGINE=InnoDB;

-- Mensajes del chat (soporte)
CREATE TABLE IF NOT EXISTS chat_mensajes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NULL,
  nombre VARCHAR(100) NULL,
  mensaje TEXT NOT NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  es_admin TINYINT(1) NOT NULL DEFAULT 0,
  sala VARCHAR(64) NOT NULL DEFAULT 'publico'
) ENGINE=InnoDB;

-- Datos de muestra
INSERT IGNORE INTO categorias (nombre) VALUES ('Tortas'), ('Repostería'), ('Snacks');
INSERT IGNORE INTO productos (categoria_id, nombre, descripcion, precio, stock, imagen, popularidad)
VALUES
 (1, 'Torta de Chocolate', 'Deliciosa torta húmeda de cacao.', 120.00, 20, 'torta_chocolate.jpg', 15),
 (2, 'Cheesecake', 'Clásico cheesecake con coulis de fresa.', 95.00, 12, 'cheesecake.jpg', 10),
 (3, 'Galletas de Mantequilla', 'Paquete de 12 galletas.', 30.00, 50, 'galletas.jpg', 25);

-- Usuario admin por defecto (password: admin123)
INSERT IGNORE INTO usuarios (nombre, email, password_hash, rol)
VALUES ('Administrador', 'admin@mitortuga.com',
        '$2y$12$WpaK/4OCe4L5/dSUEZ247.F78vUfqB6VJl19ZBI9MxEU0ZBGUM5US', 'admin');
