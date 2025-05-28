<?php
require_once '../config/sesion.php';
require_once '../config/database.php';

// Verificar autenticación
requiereAutenticacion();

$usuario_id = obtenerUsuarioId();

// Obtener fotos del usuario
$conexion = conectarDB();
$stmt = $conexion->prepare("SELECT id, nombre, ruta, descripcion FROM fotos WHERE usuario_id = ? ORDER BY fecha_subida DESC");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

$fotos = [];
while ($foto = $resultado->fetch_assoc()) {
    $fotos[] = $foto;
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Álbum de Fotos - Recuerda+</title>
    <link rel="stylesheet" href="../css/styles.css">
    <script src="../js/menu.js" defer></script>
    <script src="../js/album.js" defer></script>
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
            <a href="album.php" class="nav-link active">Álbum de fotos</a>
            <a href="calculo.php" class="nav-link">Cálculo rápido</a>
            <a href="../auth/logout.php" id="logout" class="nav-link">Cerrar sesión</a>
        </nav>
        <div class="menu-toggle" id="menu-toggle">☰</div>
    </header>

    <div class="section">
        <h1>📸 Álbum de Fotos</h1>
        <div class="upload-section">
            <form id="upload-form" action="../api/fotos.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="foto">Seleccionar imagen</label>
                    <input type="file" id="foto" name="foto" accept="image/*" required>
                </div>
                <div class="form-group">
                    <label for="nombre">Nombre de la foto</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej: Vacaciones 2025">
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" placeholder="Describe tu foto..."></textarea>
                </div>
                <button type="submit" class="btn-primary">Subir Foto</button>
            </form>
        </div>
    </div>
    
    <main>
        <div class="gallery">
            <?php if (empty($fotos)): ?>
                <p class="no-photos">No hay fotos en tu álbum. ¡Sube algunas!</p>
            <?php else: ?>
                <?php foreach ($fotos as $foto): ?>
                    <div class="photo" data-id="<?php echo $foto['id']; ?>">
                        <img src="../<?php echo htmlspecialchars($foto['ruta']); ?>" alt="<?php echo htmlspecialchars($foto['nombre']); ?>">
                        <div class="photo-info">
                            <h3><?php echo htmlspecialchars($foto['nombre']); ?></h3>
                            <?php if (!empty($foto['descripcion'])): ?>
                                <p><?php echo htmlspecialchars($foto['descripcion']); ?></p>
                            <?php endif; ?>
                            <button class="delete-photo" data-id="<?php echo $foto['id']; ?>">Eliminar</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
    
    <div id="lightbox" class="hidden">
        <span id="close">&times;</span>
        <img id="lightbox-img" src="/placeholder.svg" alt="Ampliación de imagen">
        <div id="lightbox-info"></div>
    </div>

    <footer>
        <p>© 2025 Recuerda+ - Todos los derechos reservados</p>
    </footer>
</body>
</html>
