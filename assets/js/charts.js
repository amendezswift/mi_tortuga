// assets/js/charts.js
(async function(){
  const v = await (await fetch('/mi_tortuga/controllers/charts.php?type=ventas30')).json();
  const t = await (await fetch('/mi_tortuga/controllers/charts.php?type=top')).json();

  const ctx1 = document.getElementById('ventasChart');
  new Chart(ctx1, { type:'line', data:{ labels:v.labels, datasets:[{ label:'Ventas Q', data:v.data }] }, options:{ responsive:true } });

  const ctx2 = document.getElementById('topChart');
  new Chart(ctx2, { type:'bar', data:{ labels:t.labels, datasets:[{ label:'Unidades', data:t.data }] }, options:{ responsive:true } });
})();
