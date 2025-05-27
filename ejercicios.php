<?php
require_once 'config/sesion.php';
require_once 'config/database.php';

// Verificar autenticación
requiereAutenticacion();

$usuario_id = obtenerUsuarioId();
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';

// Obtener ejercicios realizados
$conexion = conectarDB();
$stmt = $conexion->prepare("
    SELECT juego, MAX(puntuacion) as mejor_puntuacion, DATE_FORMAT(fecha_creacion, '%d/%m/%Y') as fecha_formateada
    FROM puntuaciones 
    WHERE usuario_id = ?
    GROUP BY juego
    ORDER BY fecha_creacion DESC
    LIMIT 5
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

$ejercicios_realizados = [];
while ($ejercicio = $resultado->fetch_assoc()) {
    $ejercicios_realizados[] = $ejercicio;
}
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejercicios - Recuerda+</title>
    <link rel="stylesheet" href="css/ejercicios.css">
     <link rel="stylesheet" href="css/generales.css">
    <script src="js/menu.js" defer></script>
    <script src="js/ejercicios.js" defer></script>
</head>
<body>
    <header>
        <div class="logo">
            <img src="img/logo.png" alt="Recuerda+">
            <span>Recuerda+</span>
        </div>
        <nav class="nav">
            <a href="tablero.php" class="nav-link">Inicio</a>
            <a href="agenda.php" class="nav-link">Agenda</a>
            <a href="recordatorios.php" class="nav-link">Recordatorios</a>
            <a href="ejercicios.php" class="nav-link active">Ejercicios</a>
            <a href="foro.php" class="nav-link">Foro</a>
            <a href="configuracion.php" class="nav-link">Configuración</a>
            <a href="auth/logout.php" id="logout" class="nav-link">Cerrar sesión</a>
        </nav>
        <div class="menu-toggle" id="menu-toggle">☰</div>
    </header>

    <main>
        <section class="hero">
            <div class="left-img">
                <img src="img/image 1.png" alt="Ilustración izquierda">
            </div>
            <div class="text">
                <h1>¡Ejercita tu mente!</h1>
                <p>Selecciona un juego para empezar</p>
            </div>
            <div class="right-img">
                <img src="img/abuelo.png" alt="Ilustración de abuelo">
            </div>
        </section>
        
        <section class="juegos">
            <h2>Juegos Disponibles</h2>
            <div class="cards">
                <!-- Memorama -->
                <a href="ejercicios/memorama.php" class="card facil">
                    <p class="nivel">Nivel: Fácil</p>
                    <img src="img/memorama.png" alt="Memorama" />
                    <h3>Memorama</h3>
                </a>

                <a href="ejercicios/album.php" class="card facil">
                    <p class="nivel">Nivel: Fácil</p>
                    <img src="img/album.png" alt="album" />
                    <h3>Album de fotos</h3>
                </a>
        
                <!-- Asociación de Palabras -->
                <a href="ejercicios/adivinanzas.php" class="card medio">
                    <p class="nivel">Nivel: Medio</p>
                    <img src="img/asociar.png" alt="Asociar Palabras" />
                    <h3>Adivinanzas</h3>
                </a>
        
                <!-- Cálculo Rápido -->
                <a href="ejercicios/calculo.php" class="card dificil">
                    <p class="nivel">Nivel: Difícil</p>
                    <img src="img/mates.png" alt="Cálculo Rápido" />
                    <h3>Cálculo Rápido</h3>
                </a>

                <a href="ejercicios/tetris.php" class="card dificil">
                    <p class="nivel">Nivel: Difícil</p>
                    <img src="img/tetris.png" alt="tetris">
                    <h3>TETRIS</h3>
                </a>    
            </div>
        </section>
        
        <section class="ejercicios">
            <h2>Tus Ejercicios Realizados</h2>
            <div class="cards">
                <?php if (empty($ejercicios_realizados)): ?>
                    <p>No has realizado ningún ejercicio todavía. ¡Comienza a jugar!</p>
                <?php else: ?>
                    <?php foreach ($ejercicios_realizados as $ejercicio): ?>
                        <div class="card completado">
                            <img src="img/<?php echo strtolower($ejercicio['juego']); ?>.png" alt="<?php echo htmlspecialchars($ejercicio['juego']); ?>" />
                            <h3><?php echo htmlspecialchars(ucfirst($ejercicio['juego'])); ?></h3>
                            <p>Completado</p>
                            <p><strong>Mejor puntuación:</strong> <?php echo htmlspecialchars($ejercicio['mejor_puntuacion']); ?></p>
                            <p>📅 <?php echo htmlspecialchars($ejercicio['fecha_formateada']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
        
        <section class="opiniones">
            <h2>Opiniones</h2>
            <div class="comentarios">
                <div class="comentario">
                    <p><strong>Usuario1 ⭐⭐⭐⭐⭐</strong></p>
                    <p>¡Excelente juego para ejercitar la mente!</p>
                </div>
                <div class="comentario">
                    <p><strong>Usuario2 ⭐⭐⭐⭐</strong></p>
                    <p>Me ha ayudado mucho a mantener mi mente activa</p>
                </div>
            </div>
        </section>
        
        <section class="actividad">
            <h2>¡Mantente activo!</h2>
            <div class="actividades">
                <div>
                    <p>😊</p>
                    <h3>Memorama</h3>
                    <p>Completado</p>
                </div>
                <div>
                    <p>🧠</p>
                    <h3>Asociación de Palabras</h3>
                    <p>En proceso</p>
                </div>
                <div>
                    <p>🔢</p>
                    <h3>Cálculo Rápido</h3>
                    <p>Pendiente</p>
                </div>
            </div>
        </section>
    </main>

    <div class="foot">
        <p>© 2025 Recuerda+ - Todos los derechos reservados</p>
    </div>
</body>
</html>
