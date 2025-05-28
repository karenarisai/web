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

// Verificar si la columna 'tipo' existe, si no, añadirla
$result = $conexion->query("SHOW COLUMNS FROM eventos LIKE 'tipo'");
if ($result->num_rows == 0) {
    $conexion->query("ALTER TABLE eventos ADD COLUMN tipo VARCHAR(50) DEFAULT 'otro' AFTER completado");
}

// Verificar si la columna 'detalles' existe, si no, añadirla
$result = $conexion->query("SHOW COLUMNS FROM eventos LIKE 'detalles'");
if ($result->num_rows == 0) {
    $conexion->query("ALTER TABLE eventos ADD COLUMN detalles JSON AFTER tipo");
}

// Obtener eventos
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $fecha = isset($_GET['fecha']) ? limpiarDatos($_GET['fecha']) : null;
    
    if ($fecha) {
        // Obtener eventos de una fecha específica
        $stmt = $conexion->prepare("SELECT id, titulo, fecha, hora, descripcion, completado, tipo, detalles FROM eventos WHERE usuario_id = ? AND fecha = ? ORDER BY hora");
        $stmt->bind_param("is", $usuario_id, $fecha);
    } else {
        // Obtener todos los eventos del usuario
        $stmt = $conexion->prepare("
    SELECT id, titulo, fecha, hora, descripcion, completado, tipo, detalles
    FROM eventos 
    WHERE usuario_id = ? 
    ORDER BY fecha, hora
");
        $stmt->bind_param("i", $usuario_id);
    }
    
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $eventos = [];
    while ($evento = $resultado->fetch_assoc()) {
        $eventos[] = $evento;
    }
    
    header('Content-Type: application/json');
    echo json_encode($eventos);
}

// Crear evento
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del cuerpo de la solicitud
    $datos = json_decode(file_get_contents('php://input'), true);
    
    if (!$datos) {
        // Si no hay JSON, intentar con POST normal
        $datos = [
            'titulo' => $_POST['titulo'] ?? '',
            'fecha' => $_POST['fecha'] ?? '',
            'hora' => $_POST['hora'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'completado' => $_POST['completado'] ?? 0,
            'tipo' => $_POST['tipo'] ?? 'otro',
            'detalles' => $_POST['detalles'] ?? null
        ];
    }
    
    // Validar datos
    if (empty($datos['titulo']) || empty($datos['fecha']) || empty($datos['hora'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Faltan datos requeridos']);
        exit;
    }
    
    // Insertar evento
    $stmt = $conexion->prepare("INSERT INTO eventos (usuario_id, titulo, fecha, hora, descripcion, completado, tipo, detalles) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $completado = isset($datos['completado']) ? $datos['completado'] : 0;
    $tipo = isset($datos['tipo']) ? $datos['tipo'] : 'otro';
    $detalles = isset($datos['detalles']) ? $datos['detalles'] : null;
    $stmt->bind_param("issssiss", $usuario_id, $datos['titulo'], $datos['fecha'], $datos['hora'], $datos['descripcion'], $completado, $tipo, $detalles);
    
    if ($stmt->execute()) {
        $evento_id = $conexion->insert_id;
        
        // Devolver el evento creado
        $stmt = $conexion->prepare("SELECT id, titulo, fecha, hora, descripcion, completado, tipo, detalles FROM eventos WHERE id = ?");
        $stmt->bind_param("i", $evento_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $evento = $resultado->fetch_assoc();
        
        header('Content-Type: application/json');
        echo json_encode($evento);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error al crear el evento']);
    }
}

// Actualizar evento
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $datos = json_decode(file_get_contents('php://input'), true);
    $evento_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if (!$evento_id || !$datos) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID de evento no válido o datos faltantes']);
        exit;
    }
    
    // Verificar que el evento pertenece al usuario
    $stmt = $conexion->prepare("SELECT id FROM eventos WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $evento_id, $usuario_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Evento no encontrado o no autorizado']);
        exit;
    }
    
    // Actualizar evento
    $campos = [];
    $tipos = "";
    $valores = [];
    
    if (isset($datos['titulo'])) {
        $campos[] = "titulo = ?";
        $tipos .= "s";
        $valores[] = $datos['titulo'];
    }
    
    if (isset($datos['fecha'])) {
        $campos[] = "fecha = ?";
        $tipos .= "s";
        $valores[] = $datos['fecha'];
    }
    
    if (isset($datos['hora'])) {
        $campos[] = "hora = ?";
        $tipos .= "s";
        $valores[] = $datos['hora'];
    }
    
    if (isset($datos['descripcion'])) {
        $campos[] = "descripcion = ?";
        $tipos .= "s";
        $valores[] = $datos['descripcion'];
    }
    
    if (isset($datos['completado'])) {
        $campos[] = "completado = ?";
        $tipos .= "i";
        $valores[] = $datos['completado'] ? 1 : 0;
    }

    if (isset($datos['tipo'])) {
        $campos[] = "tipo = ?";
        $tipos .= "s";
        $valores[] = $datos['tipo'];
    }

    if (isset($datos['detalles'])) {
        $campos[] = "detalles = ?";
        $tipos .= "s";
        $valores[] = $datos['detalles'];
    }
    
    if (empty($campos)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No hay datos para actualizar']);
        exit;
    }
    
    $sql = "UPDATE eventos SET " . implode(", ", $campos) . " WHERE id = ? AND usuario_id = ?";
    $tipos .= "ii";
    $valores[] = $evento_id;
    $valores[] = $usuario_id;
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param($tipos, ...$valores);
    
    if ($stmt->execute()) {
        // Devolver el evento actualizado
        $stmt = $conexion->prepare("SELECT id, titulo, fecha, hora, descripcion, completado, tipo, detalles FROM eventos WHERE id = ?");
        $stmt->bind_param("i", $evento_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $evento = $resultado->fetch_assoc();
        
        header('Content-Type: application/json');
        echo json_encode($evento);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error al actualizar el evento']);
    }
}

// Eliminar evento
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $evento_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if (!$evento_id) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID de evento no válido']);
        exit;
    }
    
    // Verificar que el evento pertenece al usuario
    $stmt = $conexion->prepare("SELECT id FROM eventos WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $evento_id, $usuario_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Evento no encontrado o no autorizado']);
        exit;
    }
    
    // Eliminar evento
    $stmt = $conexion->prepare("DELETE FROM eventos WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $evento_id, $usuario_id);
    
    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Evento eliminado correctamente']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error al eliminar el evento']);
    }
}

// Método no permitido
else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Método no permitido']);
}

$conexion->close();
?>
