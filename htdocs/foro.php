<?php
require_once 'config/sesion.php';
require_once 'config/database.php';

// Verificar autenticación
requiereAutenticacion();

$usuario_id = obtenerUsuarioId();
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';

// Obtener publicaciones del foro
$conexion = conectarDB();
$stmt = $conexion->prepare("
    SELECT p.id, p.titulo, p.contenido, p.categoria, p.fecha_creacion, p.usuario_id,
           u.nombre, u.apellidos, 
           (SELECT COUNT(*) FROM foro_likes WHERE publicacion_id = p.id) as likes,
           (SELECT COUNT(*) FROM foro_comentarios WHERE publicacion_id = p.id) as comentarios
    FROM foro_publicaciones p
    JOIN usuarios u ON p.usuario_id = u.id
    ORDER BY p.fecha_creacion DESC
");
$stmt->execute();
$publicaciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener categorías y conteo
$stmt = $conexion->prepare("
    SELECT categoria, COUNT(*) as total 
    FROM foro_publicaciones 
    GROUP BY categoria
");
$stmt->execute();
$categorias = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Verificar si la tabla existe, si no, crearla
$tablas_requeridas = [
    "foro_publicaciones" => "
        CREATE TABLE IF NOT EXISTS foro_publicaciones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            titulo VARCHAR(255) NOT NULL,
            contenido TEXT NOT NULL,
            categoria VARCHAR(50) NOT NULL,
            fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        )
    ",
    "foro_comentarios" => "
        CREATE TABLE IF NOT EXISTS foro_comentarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            publicacion_id INT NOT NULL,
            usuario_id INT NOT NULL,
            contenido TEXT NOT NULL,
            fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (publicacion_id) REFERENCES foro_publicaciones(id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        )
    ",
    "foro_likes" => "
        CREATE TABLE IF NOT EXISTS foro_likes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            publicacion_id INT NOT NULL,
            usuario_id INT NOT NULL,
            fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY (publicacion_id, usuario_id),
            FOREIGN KEY (publicacion_id) REFERENCES foro_publicaciones(id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        )
    "
];

foreach ($tablas_requeridas as $tabla => $sql) {
    $conexion->query($sql);
}

// Si no hay publicaciones, insertar algunas de ejemplo
if (count($publicaciones) == 0) {
    $ejemplos = [
        [
            'titulo' => 'Consejos para el cuidado nocturno',
            'contenido' => 'Comparto mi experiencia sobre cómo organizar los turnos nocturnos para el cuidado de personas mayores. Es importante establecer rutinas claras y asegurarse de que la persona se sienta segura durante la noche.',
            'categoria' => 'Cuidados'
        ],
        [
            'titulo' => 'Recetas saludables para personas mayores',
            'contenido' => 'Les comparto algunas recetas fáciles y nutritivas que he preparado para mi abuela. Son bajas en sodio pero muy sabrosas, y ayudan a mantener una buena alimentación.',
            'categoria' => 'Alimentación'
        ],
        [
            'titulo' => 'Ejercicios suaves para la movilidad',
            'contenido' => 'He encontrado estos ejercicios que son perfectos para personas mayores con movilidad reducida. Se pueden hacer sentados y ayudan a mantener la flexibilidad de las articulaciones.',
            'categoria' => 'Ejercicios'
        ]
    ];

    $stmt = $conexion->prepare("INSERT INTO foro_publicaciones (usuario_id, titulo, contenido, categoria) VALUES (?, ?, ?, ?)");
    
    foreach ($ejemplos as $ejemplo) {
        $stmt->bind_param("isss", $usuario_id, $ejemplo['titulo'], $ejemplo['contenido'], $ejemplo['categoria']);
        $stmt->execute();
    }

    // Recargar publicaciones
    $stmt = $conexion->prepare("
        SELECT p.id, p.titulo, p.contenido, p.categoria, p.fecha_creacion, p.usuario_id,
               u.nombre, u.apellidos, 
               (SELECT COUNT(*) FROM foro_likes WHERE publicacion_id = p.id) as likes,
               (SELECT COUNT(*) FROM foro_comentarios WHERE publicacion_id = p.id) as comentarios
        FROM foro_publicaciones p
        JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY p.fecha_creacion DESC
    ");
    $stmt->execute();
    $publicaciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Recargar categorías
    $stmt = $conexion->prepare("
        SELECT categoria, COUNT(*) as total 
        FROM foro_publicaciones 
        GROUP BY categoria
    ");
    $stmt->execute();
    $categorias = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$conexion->close();

// Función para formatear fecha
function formatearFecha($fecha) {
    $fecha_obj = new DateTime($fecha);
    $ahora = new DateTime();
    $diff = $ahora->diff($fecha_obj);
    
    if ($diff->y > 0) {
        return "Hace " . $diff->y . " año" . ($diff->y > 1 ? "s" : "");
    } elseif ($diff->m > 0) {
        return "Hace " . $diff->m . " mes" . ($diff->m > 1 ? "es" : "");
    } elseif ($diff->d > 0) {
        return "Hace " . $diff->d . " día" . ($diff->d > 1 ? "s" : "");
    } elseif ($diff->h > 0) {
        return "Hace " . $diff->h . " hora" . ($diff->h > 1 ? "s" : "");
    } elseif ($diff->i > 0) {
        return "Hace " . $diff->i . " minuto" . ($diff->i > 1 ? "s" : "");
    } else {
        return "Hace un momento";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foro - Recuerda+</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/foro.css">
    <link rel="stylesheet" href="../css/generales.css">
    <script src="js/menu.js" defer></script>
    <script src="js/foro.js" defer></script>
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
            <a href="ejercicios.php" class="nav-link">Ejercicios</a>
            <a href="foro.php" class="nav-link active">Foro</a>
            <a href="configuracion.php" class="nav-link">Configuración</a>
            <a href="auth/logout.php" id="logout" class="nav-link">Cerrar sesión</a>
        </nav>
        <div class="menu-toggle" id="menu-toggle">☰</div>
    </header>

    <main>
        <div class="foro-header">  
            <div class="search">
                <h1><i class="fas fa-bullhorn"></i> <span>Foro</span></h1>
                <input type="text" placeholder="🔍 Buscar en el foro..." class="search-bar" id="search-input">
            </div>
            <div class="nva">
                <button class="btn pink" id="openModal"><i class="fas fa-plus"></i> Nueva Publicación</button>
                <img src="img/avatar.png" alt="Usuario" class="user-avatar">
            </div>   
        </div>
        
        <div id="postModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Nueva Publicación</h2>
                <form id="new-post-form" action="api/foro.php" method="POST">
                    <input type="text" id="postTitle" name="titulo" placeholder="Título de la publicación" required>
                    <textarea id="postContent" name="contenido" placeholder="Escribe tu contenido..." required></textarea>
                    <select id="postCategory" name="categoria">
                        <option value="Cuidados"><i class="fas fa-heart"></i> Cuidados</option>
                        <option value="Alimentación"><i class="fas fa-utensils"></i> Alimentación</option>
                        <option value="Ejercicios"><i class="fas fa-dumbbell"></i> Ejercicios</option>
                        <option value="Otros"><i class="fas fa-thumbtack"></i> Otros</option>
                    </select>
                    <button type="submit" id="submitPost">Publicar</button>
                </form>
            </div>
        </div>
        
        <div class="foro-content">
            <aside class="categories">
                <h3>Categorías</h3>
                <ul>
                    <li class="category-filter active" data-category="Todos"><i class="fas fa-thumbtack"></i> Todos <span><?php echo count($publicaciones); ?></span></li>
                    <?php foreach ($categorias as $categoria): ?>
                        <li class="category-filter" data-category="<?php echo htmlspecialchars($categoria['categoria']); ?>">
                            <?php 
                                $icon = '<i class="fas fa-thumbtack"></i>';
                                switch ($categoria['categoria']) {
                                    case 'Cuidados': $icon = '<i class="fas fa-heart"></i>'; break;
                                    case 'Alimentación': $icon = '<i class="fas fa-utensils"></i>'; break;
                                    case 'Ejercicios': $icon = '<i class="fas fa-dumbbell"></i>'; break;
                                }
                                echo $icon . ' ' . htmlspecialchars($categoria['categoria']); 
                            ?> 
                            <span><?php echo $categoria['total']; ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>
            
            <section class="posts">
                <?php if (empty($publicaciones)): ?>
                    <p class="no-posts">No hay publicaciones todavía. ¡Sé el primero en publicar!</p>
                <?php else: ?>
                    <?php foreach ($publicaciones as $publicacion): ?>
                        <div class="post-card" data-id="<?php echo $publicacion['id']; ?>" data-category="<?php echo htmlspecialchars($publicacion['categoria']); ?>">
                            <img src="img/avatar.png" alt="<?php echo htmlspecialchars($publicacion['nombre'] . ' ' . $publicacion['apellidos']); ?>">
                            <div class="post-info">
                                <h4><?php echo htmlspecialchars($publicacion['nombre'] . ' ' . $publicacion['apellidos']); ?> 
                                    <span><?php echo formatearFecha($publicacion['fecha_creacion']); ?></span>
                                    <?php if ($publicacion['usuario_id'] == $usuario_id): ?>
                                        <button class="delete-post-btn" data-id="<?php echo $publicacion['id']; ?>"><i class="fas fa-trash-alt"></i></button>
                                    <?php endif; ?>
                                </h4>
                                <h3><?php echo htmlspecialchars($publicacion['titulo']); ?></h3>
                                <p><?php echo htmlspecialchars($publicacion['contenido']); ?></p>
                                <div class="post-footer">
                                    <span class="like-button" data-id="<?php echo $publicacion['id']; ?>" data-count="<?php echo $publicacion['likes']; ?>"><i class="fas fa-thumbs-up"></i> <?php echo $publicacion['likes']; ?></span>
                                    <span class="comment-button" data-id="<?php echo $publicacion['id']; ?>"><i class="fas fa-comment"></i> <?php echo $publicacion['comentarios']; ?></span>
                                    <span class="tag <?php echo strtolower(str_replace('ó', 'o', $publicacion['categoria'])); ?>"><?php echo htmlspecialchars($publicacion['categoria']); ?></span>
                                </div>
                                <div class="comments-section" style="display: none;">
                                    <div class="comments" data-id="<?php echo $publicacion['id']; ?>">
                                        <!-- Los comentarios se cargarán dinámicamente -->
                                    </div>
                                    <input type="text" class="comment-input" placeholder="Escribe un comentario...">
                                    <button class="add-comment" data-id="<?php echo $publicacion['id']; ?>">Comentar</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <div class="foot">
        © 2025 Recuerda+ - Todos los derechos reservados
    </div>
</body>
</html>
