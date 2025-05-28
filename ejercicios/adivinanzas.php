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
    <title>Adivinanzas - Recuerda+</title>
    <link rel="stylesheet" href="../css/styles.css">
    <script src="../js/menu.js" defer></script>
    <script src="../js/adivinanzas.js" defer></script>
    <style>
        .adivinanza-container {
            text-align: center;
            margin: 50px auto;
            background: #9AAF86;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            width: 50%;
        }

        #pregunta {
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
        }

        #opciones {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 20px;
        }

        #opciones button {
            background-color: #F66A94;
            border: none;
            padding: 15px;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }

        #opciones button:hover {
            background-color: #F66A94;
            transform: scale(1.05);
        }

        #mensaje {
            font-size: 20px;
            margin: 20px 0;
            min-height: 30px;
        }

        #nueva {
            background-color: #166534;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 18px;
            cursor: pointer;
            border-radius: 8px;
            transition: 0.3s ease;
            margin-top: 20px;
        }

        #nueva:hover {
            background-color: #0E4429;
            transform: scale(1.05);
        }
        
        @media (max-width: 768px) {
            .adivinanza-container {
                width: 80%;
            }
        }
    </style>
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
            <a href="adivinanzas.php" class="nav-link active">Adivinanzas</a>
            <a href="album.php" class="nav-link">Álbum de fotos</a>
            <a href="calculo.php" class="nav-link">Cálculo rápido</a>
            <a href="../auth/logout.php" id="logout" class="nav-link">Cerrar sesión</a>
        </nav>
        <div class="menu-toggle" id="menu-toggle">☰</div>
    </header>

    <section class="adivinanza-container">
        <h1>Adivinanzas</h1>
        <p id="pregunta"></p>
        <div id="opciones"></div>
        <p id="mensaje"></p>
        <button id="nueva">Nueva Adivinanza</button>
    </section>

    <footer>
        <p>© 2025 Recuerda+ - Todos los derechos reservados</p>
    </footer>
</body>
</html>
