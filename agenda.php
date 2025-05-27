<?php
require_once 'config/sesion.php';
require_once 'config/database.php';

// Verificar autenticación
requiereAutenticacion();

$usuario_id = obtenerUsuarioId();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda - Recuerda+</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/generales.css">
    <script src="js/menu.js" defer></script>
    <script src="js/agenda.js" defer></script>
</head>
<body>
    <header>
        <div class="logo">
            <img src="img/logo.png" alt="Recuerda+">
            <span>Recuerda+</span>
        </div>
        <nav class="nav">
            <a href="tablero.php" class="nav-link">Inicio</a>
            <a href="agenda.php" class="nav-link active">Agenda</a>
            <a href="recordatorios.php" class="nav-link">Recordatorios</a>
            <a href="ejercicios.php" class="nav-link">Ejercicios</a>
            <a href="foro.php" class="nav-link">Foro</a>
            <a href="configuracion.php" class="nav-link">Configuración</a>
            <a href="auth/logout.php" id="logout" class="nav-link">Cerrar sesión</a>
        </nav>
        <div class="menu-toggle" id="menu-toggle">☰</div>
    </header>

    <section class="hero">
        <div class="left-img">
            <img src="img/abuelo2.png" alt="Ilustración izquierda">
        </div>
        <div class="text">
            <h1>Mi Agenda</h1>
            <p>Organiza tus actividades, medicamentos y citas médicas</p>
        </div>
        <div class="right-img">
            <img src="img/image 1.png" alt="Ilustración de abuelo">
        </div>
    </section>

    <div class="container">
        <div class="seccion-cal">
            <div class="calendar-container">
                <div class="section-header" style="background-color: #656b4d;">
                    <h2 id="current-month"></h2>
                    <div class="calendar-nav">
                        <button id="prev-month">◀</button>
                        <button id="next-month">▶</button>
                    </div>
                </div>
                <div class="section-content">
                    <div id="calendar" class="calendar"></div>
                </div>
            </div>

            <div class="event-section">
                <div class="section-header" style="background-color: #656b4d;">
                    <h2>Eventos del día <span id="selected-date"></span></h2>
                </div>
                <div class="section-content">
                    <ul id="event-list"></ul>
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <h2>Añadir un evento</h2>
            <form id="event-form">
                <div class="form-group">
                    <label for="titulo">Título</label>
                    <input type="text" id="titulo" required>
                </div>

                <div class="form-group">
                    <label for="fecha">Fecha</label>
                    <input type="date" id="fecha" required>
                </div>

                <div class="form-group">
                    <label for="hora">Hora</label>
                    <input type="time" id="hora" required>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion"></textarea>
                </div>

                <button type="submit" class="crear-btn">＋ Crear evento</button>
            </form>
        </div>
    </div>

    <div class="foot">
        <p>© 2025 Recuerda+ - Todos los derechos reservados</p>
</div>
</body>
</html>
