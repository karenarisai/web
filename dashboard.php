<?php
require_once '../config/database.php';
require_once '../config/sesion.php';

iniciarSesion();

if (!estaAutenticado() || $_SESSION['usuario_rol'] !== 'administrador') {
    header('Location: login_admin.php');
    exit;
}

$conexion = conectarDB();
$usuarios = $conexion->query("SELECT id, nombre, email, rol, creado_en FROM usuarios ORDER BY creado_en DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Administrador</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <header>
        <h1>Panel del Administrador</h1>
        <p>Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?></p>
        <a href="../logout.php">Cerrar sesión</a>
    </header>

    <main>
        <section class="resumen">
            <h2>Resumen de Usuarios</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($usuario = $usuarios->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                        <td><?= htmlspecialchars($usuario['email']) ?></td>
                        <td><?= ucfirst($usuario['rol']) ?></td>
                        <td><?= $usuario['creado_en'] ?></td>
                        <td>
                            <a href="editar_usuario.php?id=<?= $usuario['id'] ?>">Editar</a> |
                            <a href="eliminar_usuario.php?id=<?= $usuario['id'] ?>" onclick="return confirm('¿Estás seguro?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
