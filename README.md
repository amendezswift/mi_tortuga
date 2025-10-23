# Mi Tortuga

Aplicación web completa para ventas en línea desarrollada con PHP 8, MySQL y Bootstrap 5. Incluye catálogo con reseñas, carrito AJAX, proceso de pago seguro con simulación SSL, gestión de usuarios con recuperación de contraseña y panel administrativo con analítica, inventario y exportes.

## Características clave

- **Interfaz responsiva:** maquetada con Bootstrap 5, paleta verde/beige, favicon y uso de Font Awesome 6.
- **Catálogo avanzado:** búsqueda, filtros por categoría/orden, valoración promedio y reseñas persistidas en MySQL mediante AJAX.
- **Carrito de compras:** altas/bajas asíncronas, subtotales, IVA y cálculo de envío automático según método seleccionado.
- **Pago seguro:** métodos tarjeta, PayPal y transferencia (simulados), checkbox legal obligatorio, resumen dinámico y creación de pedidos con número de guía.
- **Notificaciones:** envío de correo de confirmación mediante PHPMailer (configurable) y página de confirmación.
- **Gestión de usuarios:** registro, login, logout, recuperación por token y perfil con actualización de datos e historial de pedidos con tracking.
- **Inventario y alertas:** CRUD de productos para administradores, rebaja automática de stock y resaltado de inventario bajo.
- **Logística de envíos:** tres modalidades (normal, exprés, gratuito), control de estados (pendiente, enviado, entregado) y número de guía editable.
- **Soporte al cliente:** chat en vivo con PHP + AJAX y página de FAQ con guía para habilitar HTTPS en XAMPP.
- **Analítica y reportes:** dashboard con Chart.js, filtros por fecha y exportes a CSV/Excel y PDF utilizando FPDF.
- **Cumplimiento legal:** páginas de Privacidad y Términos, checkbox obligatorio en registro y compra.

## Requisitos

- PHP 8.x (probado en XAMPP).
- MySQL 8.x.
- Apache con soporte para HTTPS (ver guía en la sección FAQ).
- Composer (opcional, para instalar PHPMailer si no está disponible).

## Instalación

1. Clona o copia este proyecto dentro de `C:\xampp\htdocs\mi_tortuga`.
2. Importa la base de datos ejecutando el script [`sql/mi_tortuga.sql`](sql/mi_tortuga.sql) en MySQL. Se creará un usuario administrador `admin@mitortuga.com` con contraseña `admin123`.
3. Configura las credenciales de acceso a MySQL en [`includes/conexion.php`](includes/conexion.php) si difieren del usuario de muestra.
4. (Opcional) Instala PHPMailer para el envío real de correos:
   ```bash
   cd C:\xampp\htdocs\mi_tortuga
   composer require phpmailer/phpmailer
   ```
   Luego actualiza las credenciales SMTP en [`controllers/checkout.php`](controllers/checkout.php).
5. Para forzar HTTPS en XAMPP, genera un certificado autofirmado (`apache/makecert.bat`), configura los archivos `httpd-ssl.conf` y `httpd-vhosts.conf` apuntando a `mi_tortuga`, reinicia Apache y navega usando `https://localhost/mi_tortuga/`.

## Uso

- Accede al sitio en `https://localhost/mi_tortuga/index.php`.
- Crea una cuenta de cliente o inicia sesión como administrador (`admin@mitortuga.com` / `admin123`).
- Explora el catálogo, añade productos al carrito y finaliza la compra en la sección **Pago**.
- Desde **Mi Perfil** puedes actualizar tus datos, recuperar contraseña y seguir los envíos.
- El panel **Admin** habilita gráficos, CRUD de productos, actualizaciones de tracking y exportes CSV/PDF.

## Pruebas manuales sugeridas

1. Registro de usuario nuevo y verificación de login/logout.
2. Solicitud de recuperación y restablecimiento de contraseña mediante enlace temporal.
3. Navegación del catálogo aplicando búsqueda, filtros y creación de una reseña autenticada.
4. Flujo completo de compra: agregar productos, revisar carrito AJAX, seleccionar método de envío/pago, aceptar términos y generar pedido.
5. Verificación de correo (si PHPMailer está configurado) y confirmación de pedido.
6. Revisión del historial en el perfil: estados de envío, número de guía y progreso.
7. Uso del chat en vivo y consulta de FAQ/guía SSL.
8. Desde el panel Admin: creación/edición/eliminación de productos, actualización de tracking y exportes CSV/PDF.

## Estructura relevante

- `index.php`: enrutador simple de vistas.
- `controllers/`: lógica de autenticación, carrito, checkout, chat, reportes y reseñas.
- `views/`: plantillas PHP/HTML para cada sección pública y administrativa.
- `assets/js/`: scripts para carrito, reseñas, chat y gráficos.
- `assets/css/styles.css`: personalización de la paleta Mi Tortuga.
- `sql/mi_tortuga.sql`: creación completa de tablas y datos base.

## Créditos

- [Bootstrap 5](https://getbootstrap.com/) para la UI responsiva.
- [Font Awesome 6](https://fontawesome.com/) para íconos.
- [Chart.js](https://www.chartjs.org/) para gráficos.
- [FPDF](https://github.com/Setasign/FPDF) para la exportación PDF.
- [PHPMailer](https://github.com/PHPMailer/PHPMailer) para el envío de correos.
