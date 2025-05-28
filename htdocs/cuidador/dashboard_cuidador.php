<?php
require_once '../config/database.php';

session_start();

$conn = conectarDB();
$cuidador_id = $_SESSION['usuario_id'];

$stmt = $conn->prepare("SELECT nombre, email FROM usuarios WHERE asignado_a = ?");
$stmt->bind_param("i", $cuidador_id);
$stmt->execute();
$result = $stmt->get_result();

$adultos = [];
while ($row = $result->fetch_assoc()) {
    $adultos[] = $row;
}

echo json_encode($adultos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Cuidador</title>
    <link rel="stylesheet" href="../css/cuidador.css">
</head>
<body>

<header>
    <h1>Bienvenido, Cuidador</h1>
    <nav>
        <a href="perfil.php">Mi Perfil</a>
        <a href="logout.php">Cerrar Sesión</a>
    </nav>
</header>

<main>
    <h2>Adultos Mayores Asignados</h2>
    <ul id="listaAdultos"></ul>
</main>

<script>
document.addEventListener("DOMContentLoaded", function () {
    fetch("obtener_adultos.php")
        .then(response => response.json())
        .then(data => {
            const lista = document.getElementById("listaAdultos");
            data.forEach(adulto => {
                const li = document.createElement("li");
                li.textContent = `${adulto.nombre} - ${adulto.email}`;
                lista.appendChild(li);
            });
        });
});
</script>

</body>
</html>