<?php
require_once '../config/sesion.php';

// Verificar autenticación
requiereAutenticacion();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cálculo Rápido - Recuerda+</title>
    <link rel="stylesheet" href="../css/calculo.css">
    <script src="../js/menu.js" defer></script>
    <script src="../js/calculo.js" defer></script>
</head>
<body>
    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="Recuerda+">
            <span>Recuerda+</span>
        </div>
        <nav class="nav">
            <a href="../ejercicios.php" class="nav-link">Ejercicios</a>
            <a href="memorama.php" class="nav-link">Memorama</a>
            <a href="tetris.php" class="nav-link">Tetris</a>
            <a href="adivinanzas.php" class="nav-link">Adivinanzas</a>
            <a href="album.php" class="nav-link">Álbum de fotos</a>
            <a href="calculo.php" class="nav-link active">Cálculo rápido</a>
            <a href="../auth/logout.php" id="logout" class="nav-link">Cerrar sesión</a>
        </nav>
        <div class="menu-toggle" id="menu-toggle">☰</div>
    </header>

    <section class="game">
        <h1>Cálculo Rápido</h1>
        <p id="equation"></p>
        <div id="options"></div> <!-- Contenedor para las opciones -->
        <p id="score">Puntuación: 0</p>
        <button class="restart-btn">Reiniciar Juego</button>
    </section>

    <footer>
        <p>© 2025 Recuerda+ - Todos los derechos reservados</p>
    </footer>
</body>
</html>
