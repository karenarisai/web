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

// Obtener fotos
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conexion->prepare("SELECT id, nombre, ruta, descripcion, fecha_subida FROM fotos WHERE usuario_id = ? ORDER BY fecha_subida DESC");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $fotos = [];
    while ($foto = $resultado->fetch_assoc()) {
        $fotos[] = $foto;
    }
    
    header('Content-Type: application/json');
    echo json_encode($fotos);
}

// Subir foto
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si se ha subido un archivo
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No se ha subido ninguna imagen o ha ocurrido un error']);
        exit;
    }
    
    $nombre = limpiarDatos($_POST['nombre'] ?? 'Foto sin nombre');
    $descripcion = limpiarDatos($_POST['descripcion'] ?? '');
    
    // Validar tipo de archivo
    $tipo = $_FILES['foto']['type'];
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
    
    if (!in_array($tipo, $tipos_permitidos)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Tipo de archivo no permitido. Solo se permiten imágenes JPEG, PNG y GIF']);
        exit;
    }
    
    // Crear directorio de subida si no existe
    $directorio_subida = '../uploads/fotos/' . $usuario_id;
    if (!file_exists($directorio_subida)) {
        mkdir($directorio_subida, 0777, true);
    }
    
    // Generar nombre único para el archivo
    $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $nombre_archivo = uniqid() . '.' . $extension;
    $ruta_archivo = $directorio_subida . '/' . $nombre_archivo;
    $ruta_bd = 'uploads/fotos/' . $usuario_id . '/' . $nombre_archivo;
    
    // Mover archivo subido
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_archivo)) {
        // Insertar en la base de datos
        $stmt = $conexion->prepare("INSERT INTO fotos (usuario_id, nombre, ruta, descripcion) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $usuario_id, $nombre, $ruta_bd, $descripcion);
        
        if ($stmt->execute()) {
            $foto_id = $conexion->insert_id;
            
            // Devolver la foto subida
            $stmt = $conexion->prepare("SELECT id, nombre, ruta, descripcion, fecha_subida FROM fotos WHERE id = ?");
            $stmt->bind_param("i", $foto_id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $foto = $resultado->fetch_assoc();
            
            header('Content-Type: application/json');
            echo json_encode($foto);
        } else {
            // Error al insertar en la base de datos
            unlink($ruta_archivo); // Eliminar archivo subido
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Error al guardar la foto en la base de datos']);
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error al subir la imagen']);
    }
}

// Eliminar foto
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $foto_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if (!$foto_id) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID de foto no válido']);
        exit;
    }
    
    // Verificar que la foto pertenece al usuario y obtener ruta
    $stmt = $conexion->prepare("SELECT id, ruta FROM fotos WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $foto_id, $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Foto no encontrada o no autorizada']);
        exit;
    }
    
    $foto = $resultado->fetch_assoc();
    $ruta_archivo = '../' . $foto['ruta'];
    
    // Eliminar foto de la base de datos
    $stmt = $conexion->prepare("DELETE FROM fotos WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $foto_id, $usuario_id);
    
    if ($stmt->execute()) {
        // Eliminar archivo físico si existe
        if (file_exists($ruta_archivo)) {
            unlink($ruta_archivo);
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Foto eliminada correctamente']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error al eliminar la foto']);
    }
}

// Método no permitido
else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Método no permitido']);
}

$conexion->close();
?>
