<?php
require_once '../config/database.php';
require_once '../config/sesion.php';

iniciarSesion();

if (estaAutenticado()) {
    header('Location: ../tablero.php');
    exit;
}

$errores = '';
$enviado = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    function limpiarDatos($dato) {
        return htmlspecialchars(trim($dato));
    }

    $nombre = limpiarDatos($_POST['nombre']);
    $apellidos = limpiarDatos($_POST['apellidos']);
    $genero = limpiarDatos($_POST['genero']);
    $telefono = limpiarDatos($_POST['telefono'] ?? '');
    $email = limpiarDatos($_POST['email']);
    $password = limpiarDatos($_POST['password']);
    $fecha_nacimiento = limpiarDatos($_POST['fecha_nacimiento']);
    $rol = limpiarDatos($_POST['rol']);

    $cuidador = ''; // se asignará si aplica

    if ($rol === 'cuidador') {
        $cuidador = limpiarDatos($_POST['adulto_cuidado'] ?? '');
        if (empty($cuidador)) {
            $errores .= 'Debes indicar el nombre del adulto mayor que cuidas.<br>';
        }
    }

    if (empty($nombre) || empty($apellidos) || empty($email) || empty($password) || empty($rol)) {
        $errores .= 'Por favor rellena todos los campos obligatorios.<br>';
    }

    if (empty($errores)) {
        $conexion = conectarDB();
        if (!$conexion) {
            $errores .= 'Error de conexión con la base de datos.<br>';
        } else {
            // Verificar si el correo ya está registrado
            $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $errores .= "El correo electrónico ya está registrado.<br>";
            } else {
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt->close();

                $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, apellidos, genero, cuidador, telefono, email, password, fecha_nacimiento, rol) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssssss", $nombre, $apellidos, $genero, $cuidador, $telefono, $email, $password_hash, $fecha_nacimiento, $rol);

                if ($stmt->execute()) {
                    $enviado = true;
                } else {
                    $errores .= 'Error al guardar en la base de datos.<br>';
                }
                $stmt->close();
            }
            $conexion->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Recuerda+</title>
    <link rel="stylesheet" href="../css/generales.css">
    <link rel="stylesheet" href="../css/registro.css">
    <script src="../js/menu.js" defer></script>
</head>
<body>
    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="Recuerda+">
            <span>Recuerda+</span>
        </div>
        <nav class="nav">
            <a href="../index.php" class="nav-link">Inicio</a>
            <a href="login.php" class="nav-link">Iniciar sesión</a>
            <a href="registro.php" class="nav-link active">Registrarse</a>
        </nav>
        <div class="menu-toggle" id="menu-toggle">☰</div>
    </header>
    
    <div class="welcome">
            <div class="images"><img src="../img/image 1.png"></div>
            <div>
                <h1>Crear Cuenta</h1>
                <p>Completa tus datos para registrarte</p>
            </div>
            <div class="images"><img src="../img/abuelo.png"></div>   
        </div>
         <div class="user-type-switch">
        <button id="cuidadorBtn" class="active">Cuidador</button>
        <button id="adultoBtn">Adulto Mayor</button>
    </div>
    <div class="login-box">
        <div class="title">
        <img src="../img/icon.png">
        <h2>Únete a Recuerda+</h2>
        </div>      
        <div class="container">
        <!-- Formulario de Cuidador -->
        <form id="formCuidador" method="POST" action="registro.php">
        <input type="hidden" name="rol" value="cuidador">
            <div class="s1">
            <div class="p1">
           <label for="nombre_cuidador">Nombre:</label>
            <input type="text" id="nombre_cuidador" name="nombre" required />

            <label for="apellidos_cuidador">Apellidos:</label>
            <input type="text" id="apellidos_cuidador" name="apellidos" required />

            <label for="genero_cuidador">Género:</label>
            <select id="genero_cuidador" name="genero" required>
                <option value="" disabled selected>Seleccione</option>
                <option value="Masculino">Masculino</option>
                <option value="Femenino">Femenino</option>
                <option value="Otro">Otro</option>
            </select>

            <label for="telefono_cuidador">Teléfono:</label>
            <input type="tel" id="telefono_cuidador" name="telefono" />
            </div>
            <div class="p1">
            <label for="email_cuidador">Correo Electrónico:</label>
            <input type="email" id="email_cuidador" name="email" required />

            <label for="password_cuidador">Contraseña:</label>
            <input type="password" id="password_cuidador" name="password" required />

            <label for="fecha_nacimiento_cuidador">Fecha de Nacimiento:</label>
            <input type="date" id="fecha_nacimiento_cuidador" name="fecha_nacimiento" required />

            <label for="adulto_cuidado">Nombre del Adulto Mayor que cuidas:</label>
            <input type="text" id="adulto_cuidado" name="adulto_cuidado" required />
            </div>
            </div>
            <input type="submit" value="Registrarse" />
        </form>

        <!-- Formulario de Adulto Mayor -->
        <form id="formAdulto" method="POST" action="registro.php" style="display: none;">
        <input type="hidden" name="rol" value="adulto">
            <div class="s1">
            <div class="p1">
          <label for="nombre_adulto">Nombre:</label>
            <input type="text" id="nombre_adulto" name="nombre" required />

            <label for="apellidos_adulto">Apellidos:</label>
            <input type="text" id="apellidos_adulto" name="apellidos" required />

            <label for="genero_adulto">Género:</label>
            <select id="genero_adulto" name="genero" required>
                <option value="" disabled selected>Seleccione</option>
                <option value="Masculino">Masculino</option>
                <option value="Femenino">Femenino</option>
                <option value="Otro">Otro</option>
            </select>
            </div>
            <div class="p1">
            <label for="telefono_adulto">Teléfono:</label>
            <input type="tel" id="telefono_adulto" name="telefono" />

            <label for="email_adulto">Correo Electrónico:</label>
            <input type="email" id="email_adulto" name="email" required />

            <label for="password_adulto">Contraseña:</label>
            <input type="password" id="password_adulto" name="password" required />

            <label for="fecha_nacimiento_adulto">Fecha de Nacimiento:</label>
            <input type="date" id="fecha_nacimiento_adulto" name="fecha_nacimiento" required />
            </div>
            </div>
            <input type="submit" value="Registrarse" />
        </form>
    </div>
        <p>Al registrarte, aceptas nuestros <a href="#">Términos de Servicio</a> y <a href="#">Política de Privacidad</a>.</p>
                    <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
    </div>
</div>  
<div class="foot">
    <p>&copy; 2025 Recuerda+ - Todos los derechos reservados</p>
</div>
</body>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cuidadorBtn = document.getElementById('cuidadorBtn');
    const adultoBtn = document.getElementById('adultoBtn');
    const formCuidador = document.getElementById('formCuidador');
    const formAdulto = document.getElementById('formAdulto');

    // Función para activar un formulario y botón
    function activarFormulario(rol) {
        if (rol === 'cuidador') {
            formCuidador.style.display = 'block';
            formAdulto.style.display = 'none';
            cuidadorBtn.classList.add('active');
            adultoBtn.classList.remove('active');
        } else if (rol === 'adulto') {
            formCuidador.style.display = 'none';
            formAdulto.style.display = 'block';
            adultoBtn.classList.add('active');
            cuidadorBtn.classList.remove('active');
        }
    }

    // Eventos de los botones
    cuidadorBtn.addEventListener('click', function (e) {
        e.preventDefault(); // Evita que recargue la página si está dentro de un <form>
        activarFormulario('cuidador');
    });

    adultoBtn.addEventListener('click', function (e) {
        e.preventDefault();
        activarFormulario('adulto');
    });

    // Mostrar inicialmente el de cuidador si quieres
    activarFormulario('cuidador');
});
</script>

</html>