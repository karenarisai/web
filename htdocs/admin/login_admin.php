<?php
require_once '../config/database.php';
require_once '../config/sesion.php';

session_start();

$conn = conectarDB(); // Asegurar que la conexión esté disponible

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $res = $conn->query("SELECT * FROM usuarios WHERE email='$email' AND tipo_usuario='admin'");
    $admin = $res->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: dashboard_admin.php");
        exit();
    } else {
        $error = "Credenciales inválidas.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar como administrador- Recuerda+</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/generales.css">

</head>
<body>
    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="Recuerda+">
            <span>Recuerda+</span>
        </div>
    </header>   
    <!-- formulario de login -->
     
    <div class="container">
    
    <form method="POST">
    <div class=title>    
    <img src="../img/icon.png">
    <h3>Iniciar Sesión como Administrador</h1>
    </div>
    <input type="email" name="email" placeholder="Correo" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <button type="submit">Iniciar Sesión</button>
    </form>
    <?php if (isset($error)) echo "<p>$error</p>"; ?>
    </div>
    <div class="foot">
        <p>© 2025 Recuerda+ - Todos los derechos reservados</p>
    </div>
</body>
</html>

