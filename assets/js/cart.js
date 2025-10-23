// assets/js/cart.js
async function addToCart(producto_id, cantidad=1){
  const res = await fetch('/mi_tortuga/controllers/cart.php?action=add', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: new URLSearchParams({producto_id, cantidad})
  });
  const j = await res.json();
  if(j.ok){ alert('Agregado al carrito'); renderCartTable?.(); } else { alert(j.msg||'Error'); }
}

async function removeFromCart(producto_id){
  const res = await fetch('/mi_tortuga/controllers/cart.php?action=remove', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: new URLSearchParams({producto_id})
  });
  const j = await res.json();
  if(j.ok){ renderCartTable(); }
}

async function renderCartTable(){
  const box = document.getElementById('cart-container');
  if(!box) return;
  const res = await fetch('/mi_tortuga/controllers/cart.php?action=list');
  const j = await res.json();
  if(!j.ok || !j.items || j.items.length === 0){
    box.innerHTML = '<div class="alert alert-warning">Carrito vacío</div>';
    return;
  }
  let html = '<table class="table table-striped"><thead><tr><th>Producto</th><th>Cant</th><th>Precio</th><th>Subtotal</th><th></th></tr></thead><tbody>';
  for (const it of j.items) {
    const precio = Number(it.precio ?? 0);
    const subtotal = Number(it.subtotal ?? 0);
    html += `<tr><td>${it.nombre}</td><td>${it.cantidad}</td><td>Q ${precio.toFixed(2)}</td><td>Q ${subtotal.toFixed(2)}</td>`;
    html += `<td><button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${it.producto_id})">Quitar</button></td></tr>`;
  }
  html += `</tbody></table>
  <div class="text-end">
    <div>Subtotal: <strong>Q ${Number(j.subtotal ?? 0).toFixed(2)}</strong></div>
    <div>IVA (12%): <strong>Q ${Number(j.iva ?? 0).toFixed(2)}</strong></div>
    <div>Envío: <strong>Q ${Number(j.envio ?? 0).toFixed(2)}</strong></div>
    <div class="fs-5">Total: <strong>Q ${Number(j.total ?? 0).toFixed(2)}</strong></div>
  </div>`;
  box.innerHTML = html;
}
