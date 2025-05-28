<?php
// Iniciar sesión si no está iniciada
function iniciarSesion() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Verificar si el usuario está autenticado
function estaAutenticado() {
    iniciarSesion();
    return isset($_SESSION['usuario_id']);
}

// Redirigir si no está autenticado
function requiereAutenticacion() {
    if (!estaAutenticado()) {
        header('Location: login.php');
        exit;
    }
}

// Obtener ID del usuario actual
function obtenerUsuarioId() {
    iniciarSesion();
    return $_SESSION['usuario_id'] ?? null;
}

// Cerrar sesión
function cerrarSesion() {
    iniciarSesion();
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// Verificar si el usuario es administrador
function checkAdmin() {
    iniciarSesion();

    // Asegúrate de guardar 'tipo_usuario' en $_SESSION al iniciar sesión
    if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
        header('Location: ../admin/login_admin.php'); // O ajusta según tu estructura de carpetas
        exit;
    }
}

?>

