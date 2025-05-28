<?php
require_once '../config/database.php';
require_once '../config/sesion.php';

// Verificar autenticación para todas las solicitudes
iniciarSesion();
if (!estaAutenticado()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$usuario_id = obtenerUsuarioId();
$conexion = conectarDB();

// Verificar si la tabla existe, si no, crearla
$sql = "CREATE TABLE IF NOT EXISTS puntuaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    juego VARCHAR(50) NOT NULL,
    puntuacion INT NOT NULL,
    tiempo INT NULL,
    intentos INT NULL,
    dificultad VARCHAR(20) NULL,
    detalles JSON NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
)";
$conexion->query($sql);

// Obtener puntuaciones
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $juego = isset($_GET['juego']) ? limpiarDatos($_GET['juego']) : null;
    
    if ($juego) {
        // Obtener puntuaciones de un juego específico
        $stmt = $conexion->prepare("
            SELECT id, juego, puntuacion, tiempo, intentos, dificultad, fecha_creacion 
            FROM puntuaciones 
            WHERE usuario_id = ? AND juego = ? 
            ORDER BY puntuacion DESC 
            LIMIT 10
        ");
        $stmt->bind_param("is", $usuario_id, $juego);
    } else {
        // Obtener todas las puntuaciones del usuario
        $stmt = $conexion->prepare("
            SELECT id, juego, puntuacion, tiempo, intentos, dificultad, fecha_creacion 
            FROM puntuaciones 
            WHERE usuario_id = ? 
            ORDER BY fecha_creacion DESC 
            LIMIT 20
        ");
        $stmt->bind_param("i", $usuario_id);
    }
    
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $puntuaciones = [];
    while ($puntuacion = $resultado->fetch_assoc()) {
        $puntuaciones[] = $puntuacion;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'puntuaciones' => $puntuaciones]);
}

// Guardar puntuación
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del cuerpo de la solicitud
    $datos = json_decode(file_get_contents('php://input'), true);
    
    if (!$datos) {
        // Si no hay JSON, intentar con POST normal
        $datos = [
            'juego' => $_POST['juego'] ?? '',
            'puntuacion' => $_POST['puntuacion'] ?? 0,
            'detalles' => $_POST['detalles'] ?? null
        ];
    }
    
    // Validar datos
    if (empty($datos['juego']) || !isset($datos['puntuacion'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Faltan datos requeridos']);
        exit;
    }
    
    // Convertir puntuación a entero
    $puntuacion = intval($datos['puntuacion']);
    
    // Extraer detalles específicos
    $tiempo = null;
    $intentos = null;
    $dificultad = null;
    $detalles_json = null;
    
    if (isset($datos['detalles']) && is_array($datos['detalles'])) {
        if (isset($datos['detalles']['tiempo'])) {
            $tiempo = intval($datos['detalles']['tiempo']);
        }
        
        if (isset($datos['detalles']['intentos'])) {
            $intentos = intval($datos['detalles']['intentos']);
        }
        
        if (isset($datos['detalles']['dificultad'])) {
            $dificultad = $datos['detalles']['dificultad'];
        }
        
        $detalles_json = json_encode($datos['detalles']);
    }
    
    // Insertar puntuación
    $stmt = $conexion->prepare("
        INSERT INTO puntuaciones 
        (usuario_id, juego, puntuacion, tiempo, intentos, dificultad, detalles) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("isiisss", $usuario_id, $datos['juego'], $puntuacion, $tiempo, $intentos, $dificultad, $detalles_json);
    
    if ($stmt->execute()) {
        $puntuacion_id = $conexion->insert_id;
        
        // Devolver la puntuación creada
        $stmt = $conexion->prepare("
            SELECT id, juego, puntuacion, tiempo, intentos, dificultad, fecha_creacion 
            FROM puntuaciones 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $puntuacion_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $puntuacion = $resultado->fetch_assoc();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'puntuacion' => $puntuacion]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Error al guardar la puntuación']);
    }
}

// Método no permitido
else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}

$conexion->close();
?>
