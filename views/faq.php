<?php // views/faq.php ?>
<h2>Preguntas Frecuentes</h2>
<div class="accordion" id="faq">
  <div class="accordion-item">
    <h2 class="accordion-header"><button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#f1">¿Cómo compro?</button></h2>
    <div id="f1" class="accordion-collapse collapse show"><div class="accordion-body">Agrega productos al carrito y procede al pago.</div></div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#f2">¿Métodos de pago?</button></h2>
    <div id="f2" class="accordion-collapse collapse"><div class="accordion-body">Tarjeta, PayPal y transferencia (simulados en entorno local).</div></div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#f3">¿Cómo activo HTTPS en XAMPP?</button></h2>
    <div id="f3" class="accordion-collapse collapse"><div class="accordion-body">
      <ol class="mb-0">
        <li>Abre la terminal de XAMPP y ejecuta <code>makecert.bat</code> dentro de <code>apache</code> para crear un certificado autofirmado.</li>
        <li>Edita <code>httpd-ssl.conf</code> y apunta <code>SSLCertificateFile</code> y <code>SSLCertificateKeyFile</code> a los archivos generados.</li>
        <li>En <code>httpd-vhosts.conf</code> agrega un virtual host para <code>https://localhost/mi_tortuga</code> especificando <code>DocumentRoot "C:/xampp/htdocs/mi_tortuga"</code>.</li>
        <li>Reinicia Apache desde el panel de control y navega usando <code>https://localhost/mi_tortuga/index.php?page=pago</code>. El navegador mostrará un aviso por tratarse de un certificado autofirmado.</li>
      </ol>
    </div></div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#f4">¿Cómo funcionan los envíos?</button></h2>
    <div id="f4" class="accordion-collapse collapse"><div class="accordion-body">Ofrecemos envío normal (Q25 o gratis desde Q300), exprés (Q45) y gratuito en promociones. Puedes ver el número de guía y estado desde tu perfil.</div></div>
  </div>
</div>
