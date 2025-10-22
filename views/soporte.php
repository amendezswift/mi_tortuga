<?php // views/soporte.php ?>
<h2>Soporte en vivo</h2>
<div class="row g-3">
  <div class="col-md-4">
    <input id="chat_nombre" class="form-control" placeholder="Tu nombre (opcional)">
  </div>
  <div class="col-md-8 d-grid">
    <button class="btn btn-success" onclick="chatSend()">Enviar</button>
  </div>
  <div class="col-12">
    <textarea id="chat_mensaje" class="form-control" rows="3" placeholder="Escribe tu mensaje"></textarea>
  </div>
</div>
<div class="border rounded p-3 mt-3 bg-white" style="height:350px; overflow:auto" id="chat_box"></div>
<script src="/mi_tortuga/assets/js/chat.js"></script>
