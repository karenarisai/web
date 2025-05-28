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

$conexion = conectarDB();

// Obtener adivinanza aleatoria
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Obtener una adivinanza aleatoria
    $stmt = $conexion->prepare("SELECT id, pregunta, respuesta_correcta, opcion1, opcion2, opcion3 FROM adivinanzas ORDER BY RAND() LIMIT 1");
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $adivinanza = $resultado->fetch_assoc();
        
        // Crear array de opciones y mezclarlas
        $opciones = [
            $adivinanza['opcion1'],
            $adivinanza['opcion2'],
            $adivinanza['opcion3']
        ];
        shuffle($opciones);
        
        $respuesta = [
            'id' => $adivinanza['id'],
            'pregunta' => $adivinanza['pregunta'],
            'opciones' => $opciones,
            'respuesta_correcta' => $adivinanza['respuesta_correcta']
        ];
        
        header('Content-Type: application/json');
        echo json_encode($respuesta);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No se encontraron adivinanzas']);
    }
}

// Método no permitido
else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Método no permitido']);
}

$conexion->close();
?>
