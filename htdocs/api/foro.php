<?php
require_once '../config/sesion.php';
require_once '../config/database.php';

// Verificar autenticación para todas las solicitudes
iniciarSesion();
if (!estaAutenticado()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$usuario_id = obtenerUsuarioId();
$conexion = conectarDB();

// Crear nueva publicación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['action'])) {
    $titulo = limpiarDatos($_POST['titulo'] ?? '');
    $contenido = limpiarDatos($_POST['contenido'] ?? '');
    $categoria = limpiarDatos($_POST['categoria'] ?? 'Otros');
    
    // Validar datos
    if (empty($titulo) || empty($contenido)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Faltan datos requeridos']);
        exit;
    }
    
    // Insertar publicación
    $stmt = $conexion->prepare("INSERT INTO foro_publicaciones (usuario_id, titulo, contenido, categoria) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $usuario_id, $titulo, $contenido, $categoria);
    
    if ($stmt->execute()) {
        $publicacion_id = $conexion->insert_id;
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'id' => $publicacion_id]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Error al crear la publicación']);
    }
}

// Dar like a una publicación
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'like') {
    $publicacion_id = intval($_GET['id'] ?? 0);
    
    if (!$publicacion_id) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'ID de publicación no válido']);
        exit;
    }
    
    // Verificar si ya dio like
    $stmt = $conexion->prepare("SELECT id FROM foro_likes WHERE publicacion_id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $publicacion_id, $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        // Ya dio like, eliminar
        $stmt = $conexion->prepare("DELETE FROM foro_likes WHERE publicacion_id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $publicacion_id, $usuario_id);
        $stmt->execute();
        
        // Obtener nuevo conteo de likes
        $stmt = $conexion->prepare("SELECT COUNT(*) as likes FROM foro_likes WHERE publicacion_id = ?");
        $stmt->bind_param("i", $publicacion_id);
        $stmt->execute();
        $likes = $stmt->get_result()->fetch_assoc()['likes'];
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'likes' => $likes, 'action' => 'removed']);
    } else {
        // No ha dado like, añadir
        $stmt = $conexion->prepare("INSERT INTO foro_likes (publicacion_id, usuario_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $publicacion_id, $usuario_id);
        $stmt->execute();
        
        // Obtener nuevo conteo de likes
        $stmt = $conexion->prepare("SELECT COUNT(*) as likes FROM foro_likes WHERE publicacion_id = ?");
        $stmt->bind_param("i", $publicacion_id);
        $stmt->execute();
        $likes = $stmt->get_result()->fetch_assoc()['likes'];
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'likes' => $likes, 'action' => 'added']);
    }
}

// Añadir comentario
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'addComment') {
    $publicacion_id = intval($_GET['id'] ?? 0);
    $datos = json_decode(file_get_contents('php://input'), true);
    $contenido = limpiarDatos($datos['contenido'] ?? '');
    
    if (!$publicacion_id || empty($contenido)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
        exit;
    }
    
    // Insertar comentario
    $stmt = $conexion->prepare("INSERT INTO foro_comentarios (publicacion_id, usuario_id, contenido) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $publicacion_id, $usuario_id, $contenido);
    
    if ($stmt->execute()) {
        $comentario_id = $conexion->insert_id;
        
        // Obtener datos del usuario
        $stmt = $conexion->prepare("SELECT nombre, apellidos FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'comment' => [
                'id' => $comentario_id,
                'contenido' => $contenido,
                'nombre' => $usuario['nombre'],
                'apellidos' => $usuario['apellidos'],
                'fecha' => 'Hace un momento',
                'usuario_id' => $usuario_id
            ]
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Error al añadir comentario']);
    }
}

// Obtener comentarios
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'getComments') {
    $publicacion_id = intval($_GET['id'] ?? 0);
    
    if (!$publicacion_id) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'ID de publicación no válido']);
        exit;
    }
    
    // Obtener comentarios
    $stmt = $conexion->prepare("
        SELECT c.id, c.contenido, c.fecha_creacion, c.usuario_id, u.nombre, u.apellidos
        FROM foro_comentarios c
        JOIN usuarios u ON c.usuario_id = u.id
        WHERE c.publicacion_id = ?
        ORDER BY c.fecha_creacion ASC
    ");
    $stmt->bind_param("i", $publicacion_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $comentarios = [];
    while ($comentario = $resultado->fetch_assoc()) {
        // Formatear fecha
        $fecha_obj = new DateTime($comentario['fecha_creacion']);
        $ahora = new DateTime();
        $diff = $ahora->diff($fecha_obj);
        
        if ($diff->y > 0) {
            $fecha = "Hace " . $diff->y . " año" . ($diff->y > 1 ? "s" : "");
        } elseif ($diff->m > 0) {
            $fecha = "Hace " . $diff->m . " mes" . ($diff->m > 1 ? "es" : "");
        } elseif ($diff->d > 0) {
            $fecha = "Hace " . $diff->d . " día" . ($diff->d > 1 ? "s" : "");
        } elseif ($diff->h > 0) {
            $fecha = "Hace " . $diff->h . " hora" . ($diff->h > 1 ? "s" : "");
        } elseif ($diff->i > 0) {
            $fecha = "Hace " . $diff->i . " minuto" . ($diff->i > 1 ? "s" : "");
        } else {
            $fecha = "Hace un momento";
        }
        
        $comentario['fecha'] = $fecha;
        $comentario['current_user_id'] = $usuario_id; // Añadir ID del usuario actual para comparar
        $comentarios[] = $comentario;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'comments' => $comentarios]);
}

// Eliminar comentario
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $_GET['action'] === 'deleteComment') {
    $comentario_id = intval($_GET['id'] ?? 0);
    
    if (!$comentario_id) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'ID de comentario no válido']);
        exit;
    }
    
    // Verificar que el comentario pertenece al usuario
    $stmt = $conexion->prepare("SELECT id FROM foro_comentarios WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $comentario_id, $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'No tienes permiso para eliminar este comentario']);
        exit;
    }
    
    // Eliminar comentario
    $stmt = $conexion->prepare("DELETE FROM foro_comentarios WHERE id = ?");
    $stmt->bind_param("i", $comentario_id);
    
    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Error al eliminar el comentario']);
    }
}

// Eliminar publicación
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $_GET['action'] === 'deletePost') {
    $publicacion_id = intval($_GET['id'] ?? 0);
    
    if (!$publicacion_id) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'ID de publicación no válido']);
        exit;
    }
    
    // Verificar que la publicación pertenece al usuario
    $stmt = $conexion->prepare("SELECT id FROM foro_publicaciones WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $publicacion_id, $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'No tienes permiso para eliminar esta publicación']);
        exit;
    }
    
    // Eliminar publicación (los comentarios y likes se eliminarán automáticamente por las restricciones de clave foránea)
    $stmt = $conexion->prepare("DELETE FROM foro_publicaciones WHERE id = ?");
    $stmt->bind_param("i", $publicacion_id);
    
    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Error al eliminar la publicación']);
    }
}

// Método no permitido
else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}

$conexion->close();
?>
