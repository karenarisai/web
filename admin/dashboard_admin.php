<?php
require_once '../config/database.php';
require_once '../config/sesion.php';

$conn = conectarDB(); // <--- Esta línea es clave
checkAdmin();

// Ahora ya puedes usar $conn sin errores
$totalUsuarios = $conn->query("SELECT COUNT(*) FROM usuarios")->fetch_row()[0];
$totalEventos = $conn->query("SELECT COUNT(*) FROM eventos")->fetch_row()[0];
$totalCuidadores = $conn->query("SELECT COUNT(*) FROM usuarios WHERE tipo_usuario='cuidador'")->fetch_row()[0];
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Administrador</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    .dashboard-container {
      max-width: 800px;
      margin: 2rem auto;
    }
    .card {
      margin-bottom: 1rem;
      padding: 1rem;
      border-radius: 0.5rem;
      background-color: #f8f9fa;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>

<div class="container dashboard-container">
  <h2 class="mb-4 text-center">Dashboard Administrador</h2>

  <div class="row text-center">
    <div class="col-md-4">
      <div class="card">
        <h5>Total Usuarios</h5>
        <p class="display-6"><?= $totalUsuarios ?></p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <h5>Total Cuidadores</h5>
        <p class="display-6"><?= $totalCuidadores ?></p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <h5>Total Eventos</h5>
        <p class="display-6"><?= $totalEventos ?></p>
      </div>
    </div>
  </div>

  <div class="mt-5">
    <canvas id="grafico" height="100"></canvas>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('grafico').getContext('2d');
const chart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Usuarios', 'Cuidadores', 'Eventos'],
    datasets: [{
      label: 'Resumen General',
      data: [<?= $totalUsuarios ?>, <?= $totalCuidadores ?>, <?= $totalEventos ?>],
      backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc']
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          precision: 0
        }
      }
    }
  }
});
</script>

</body>
</html>
