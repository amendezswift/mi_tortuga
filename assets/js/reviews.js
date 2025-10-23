// assets/js/reviews.js

function openReviewModal(productId, productName){
  document.getElementById('reviewProducto').value = productId;
  document.getElementById('reviewProductoLabel').textContent = productName;
  const modal = new bootstrap.Modal(document.getElementById('reviewModal'));
  modal.show();
}

document.getElementById('reviewForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const form = e.currentTarget;
  const formData = new FormData(form);
  const res = await fetch('/mi_tortuga/controllers/reviews.php?action=create', {
    method: 'POST',
    body: new URLSearchParams(formData)
  });
  const data = await res.json();
  const alertBox = document.getElementById('reviewAlert');
  if (data.ok) {
    alertBox.className = 'alert alert-success';
    alertBox.textContent = '¡Gracias por tu opinión!';
    alertBox.classList.remove('d-none');
    form.reset();
    setTimeout(() => window.location.reload(), 1200);
  } else {
    alertBox.className = 'alert alert-danger';
    alertBox.textContent = data.msg || 'No fue posible guardar tu reseña.';
    alertBox.classList.remove('d-none');
  }
});
