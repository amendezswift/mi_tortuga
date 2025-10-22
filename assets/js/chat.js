// assets/js/chat.js
let lastId = 0;
async function chatPoll(){
  try{
    const res = await fetch('/mi_tortuga/controllers/chat.php?action=list&last='+lastId);
    const j = await res.json();
    if(j.ok){
      const box = document.getElementById('chat_box');
      for(const m of j.mensajes){
        const badge = m.es_admin ? '<span class="badge bg-success me-2">Admin</span>' : '';
        const name = (m.nombre||'Anon');
        box.innerHTML += `<div><small class="text-muted">${m.creado_en}</small> ${badge}<strong>${name}:</strong> ${m.mensaje}</div>`;
        box.scrollTop = box.scrollHeight;
      }
      lastId = j.last || lastId;
    }
  } catch(e){}
  setTimeout(chatPoll, 1500);
}
async function chatSend(){
  const nombre = document.getElementById('chat_nombre').value;
  const mensaje = document.getElementById('chat_mensaje').value;
  if(!mensaje.trim()) return;
  await fetch('/mi_tortuga/controllers/chat.php?action=send', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({nombre,mensaje})});
  document.getElementById('chat_mensaje').value='';
}
chatPoll();
