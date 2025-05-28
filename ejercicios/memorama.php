<?php
require_once '../config/sesion.php';
require_once '../config/database.php';

// Verificar autenticación
requiereAutenticacion();

$usuario_id = obtenerUsuarioId();

// Obtener mejor puntuación del usuario
$conexion = conectarDB();
$stmt = $conexion->prepare("
    SELECT MAX(puntuacion) as mejor_puntuacion, 
           MIN(tiempo) as mejor_tiempo,
           MIN(intentos) as mejor_intentos
    FROM puntuaciones 
    WHERE usuario_id = ? AND juego = 'memorama'
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$mejores = $resultado->fetch_assoc();

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memorama - Recuerda+</title>
    <link rel="stylesheet" href="../css/memorama.css">
    <script src="../js/menu.js" defer></script>
    <script src="../js/memorama.js" defer></script>
</head>
<body>
    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="Recuerda+">
            <span>Recuerda+</span>
        </div>
        <nav class="nav">
            <a href="../ejercicios.php" class="nav-link">Ejercicios</a>
            <a href="memorama.php" class="nav-link active">Memorama</a>
            <a href="tetris.php" class="nav-link">Tetris</a>
            <a href="adivinanzas.php" class="nav-link">Adivinanzas</a>
            <a href="album.php" class="nav-link">Álbum de fotos</a>
            <a href="calculo.php" class="nav-link">Cálculo rápido</a>
            <a href="../auth/logout.php" id="logout" class="nav-link">Cerrar sesión</a>
        </nav>
        <div class="menu-toggle" id="menu-toggle">☰</div>
    </header>

    <div class="memorama-container">
        <h1>Memorama</h1>
        
        <div class="memorama-info">
            <div class="memorama-stats">
                <div class="stat">
                    <span class="stat-label">Intentos:</span>
                    <span id="intentos" class="stat-value">0</span>
                </div>
                <div class="stat">
                    <span class="stat-label">Pares encontrados:</span>
                    <span id="pares" class="stat-value">0</span>
                </div>
                <div class="stat">
                    <span class="stat-label">Tiempo:</span>
                    <span id="tiempo" class="stat-value">00:00</span>
                </div>
            </div>
            
            <div class="memorama-controls">
                <select id="dificultad">
                    <option value="facil">Fácil (4x3)</option>
                    <option value="medio">Medio (4x4)</option>
                    <option value="dificil">Difícil (6x4)</option>
                </select>
                <button id="reiniciar" class="btn-primary">Reiniciar Juego</button>
            </div>
        </div>
        
        <div id="tablero" class="memorama-tablero"></div>
        
        <div id="mensaje-victoria" class="mensaje-victoria">
            <h2>¡Felicidades!</h2>
            <p>Has completado el juego en <span id="tiempo-final">00:00</span> con <span id="intentos-final">0</span> intentos.</p>
            <p>Tu puntuación: <span id="puntuacion-final">0</span> puntos</p>
            <?php if ($mejores && $mejores['mejor_puntuacion']): ?>
                <p>Tu mejor puntuación: <?php echo $mejores['mejor_puntuacion']; ?> puntos</p>
                <p>Tu mejor tiempo: <?php echo gmdate("i:s", $mejores['mejor_tiempo']); ?></p>
                <p>Tu menor número de intentos: <?php echo $mejores['mejor_intentos']; ?></p>
            <?php endif; ?>
            <button id="jugar-otra-vez" class="btn-primary">Jugar otra vez</button>
        </div>
    </div>

    <footer>
        <p>© 2025 Recuerda+ - Todos los derechos reservados</p>
    </footer>
</body>
</html>
