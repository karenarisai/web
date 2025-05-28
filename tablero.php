<?php
require_once 'config/sesion.php';
require_once 'config/database.php';

// Verificar autenticación
requiereAutenticacion();

$usuario_id = obtenerUsuarioId();
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';

// Obtener eventos próximos
$conexion = conectarDB();
$stmt = $conexion->prepare("
    SELECT id, titulo, fecha, hora 
    FROM eventos 
    WHERE usuario_id = ? AND fecha >= CURDATE() 
    ORDER BY fecha, hora 
    LIMIT 5
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

$eventos_proximos = [];
while ($evento = $resultado->fetch_assoc()) {
    $eventos_proximos[] = $evento;
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tablero - Recuerda+</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/generales.css">
    <script src="js/menu.js" defer></script>
</head>
<body>
    <header>
        <div class="logo">
            <img src="img/logo.png" alt="Recuerda+">
            <span>Recuerda+</span>
        </div>
        <nav class="nav">
            <a href="tablero.php" class="nav-link active">Inicio</a>
            <a href="agenda.php" class="nav-link">Agenda</a>
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
            <h1>Bienvenido/a, <?php echo htmlspecialchars($usuario_nombre); ?></h1>
            <p>¿Qué te gustaría hacer hoy?</p>
        </div>
        <div class="right-img">
            <img src="img/image 1.png" alt="Ilustración de abuelo">
        </div>
    </section>

    <div class="dashboard-container">
        <div class="dashboard-section">
            <h2>Próximos eventos</h2>
            <div class="event-list">
                <?php if (empty($eventos_proximos)): ?>
                    <p>No tienes eventos próximos</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($eventos_proximos as $evento): ?>
                            <li>
                                <div class="event-date">
                                    <?php 
                                        $fecha = new DateTime($evento['fecha']);
                                        echo $fecha->format('d/m/Y'); 
                                    ?>
                                </div>
                                <div class="event-time"><?php echo $evento['hora']; ?></div>
                                <div class="event-title"><?php echo htmlspecialchars($evento['titulo']); ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <a href="agenda.php" class="btn-secondary">Ver todos</a>
            </div>
        </div>

        <div class="dashboard-section">
            <h2>Ejercicios</h2>
            <div class="exercise-grid">
                <a href="ejercicios/memorama.php" class="exercise-card">
                    <img src="img/memorama.png" alt="Memorama">
                    <span>Memorama</span>
                </a>
                <a href="ejercicios/adivinanzas.php" class="exercise-card">
                    <img src="img/brain.png" alt="Adivinanzas">
                    <span>Adivinanzas</span>
                </a>
                <a href="ejercicios/album.php" class="exercise-card">
                    <img src="img/album.png" alt="Álbum de fotos">
                    <span>Álbum de fotos</span>
                </a>
                <a href="ejercicios/calculo.php" class="exercise-card">
                    <img src="img/mates.png" alt="Cálculo rápido">
                    <span>Cálculo rápido</span>
                </a>
            </div>
        </div>
    </div>

    <div class="foot">
        <p>© 2025 Recuerda+ - Todos los derechos reservados</p>
    </div>
</body>
</html>
