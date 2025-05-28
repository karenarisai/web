<?php
require_once 'config/sesion.php';

// Verificar si el usuario está autenticado
iniciarSesion();
$autenticado = estaAutenticado();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuerda+</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/generales.css">
    <script src="js/menu.js" defer></script>
</head>
<body>
    <header>
        <div class="logo">
            <img src="img/logo.png" alt="Recuerda+">
            <span>Recuerda+</span>
        </div>
        <nav class="nav">
            <a href="index.php" class="nav-link active">Inicio</a>
            <?php if ($autenticado): ?>
                <a href="tablero.php" class="nav-link">Tablero</a>
                <a href="auth/logout.php" class="nav-link">Cerrar sesión</a>
            <?php else: ?>
                <a href="auth/login.php" class="nav-link">Iniciar sesión</a>
                <a href="auth/registro.php" class="nav-link">Registrarse</a>
            <?php endif; ?>
        </nav>
        <div class="menu-toggle" id="menu-toggle">☰</div>
    </header>
    
    <div class="hero">
        <h1>¡Bienvenido a Recuerda+!</h1>
        <p>Descubre una nueva forma de cuidar a tus seres queridos</p>
    </div>

    <div class="features">
        <div class="titulod">
            <h2>Funciones Clave</h2>
            <p>Mejora la vida diaria de tus seres queridos</p>
        </div>
        
        <div class="feature-list">
            <div class="feature">
                <img src="img/cal.png" alt="Recordatorios">
                <h3>Recordatorios Personalizados</h3>
            </div>
            <div class="feature">
                <img src="img/cal2.png" alt="Agenda">
                <h3>Agenda de Actividades</h3>
            </div>
            <div class="feature">
                <img src="img/brain.png" alt="Ejercicios">
                <h3>Ejercicios Cognitivos</h3>
            </div>
        </div>
    </div>
    
    <div class="foot">
        <p>&copy; 2025 Recuerda+ - Todos los derechos reservados</p>
        </div>
</body>
</html>
