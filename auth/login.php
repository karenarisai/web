<?php
require_once '../config/database.php';
require_once '../config/sesion.php';

$conn = conectarDB(); // ← IMPORTANTE: Crear conexión antes de usarla

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = limpiarDatos($_POST['usuario']);
    $password = limpiarDatos($_POST['password']);

    // Consulta segura con prepared statement
    $stmt = $conn->prepare("SELECT id, tipo_usuario, password FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $fila = $resultado->fetch_assoc();
        if (password_verify($password, $fila['password'])) {
            session_start();
            $_SESSION['usuario_id'] = $fila['id'];
            $_SESSION['tipo_usuario'] = $fila['tipo_usuario'];

            // Redirige según el tipo de usuario
            if ($fila['tipo_usuario'] === 'admin') {
                header("Location: dashboard_admin.php");
            } else {
                header("Location: dashboard_usuario.php");
            }
            exit;
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "Usuario no encontrado.";
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Recuerda+</title>
    <link rel="stylesheet" href="../css/iniciarsesion.css">
    <link rel="stylesheet" href="../css/generales.css">
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
            <a href="login.php" class="nav-link active">Iniciar sesión</a>
            <a href="registro.php" class="nav-link">Registrarse</a>
        </nav>
        <div class="menu-toggle" id="menu-toggle">☰</div>
    </header>
    <div class="user-type-switch">
        <button id="cuidadorBtn" class="active">Cuidador</button>
        <button id="adultoBtn">Adulto Mayor</button>
    </div>

    <div class="cont"> 
        <div class="login-container">
        <div class="hero" id="heroCuidador">
          <div class="images"><img src="../img/image 1.png"></div>
          <div class="contenido">
          <h1>Bienvenido, Cuidador/Familiar</h1>
          <p>Accede para acompañar y ayudar</p>
        </div>
          <div class="images"><img src="../img/abuelo.png"></div>
        </div>

        <div class="hero hidden" id="heroAdulto">
          <div class="images"><img src="../img/image 1.png"></div>
          <div class="contenido">
          <h1>Hola, Adulto Mayor</h1>
          <p>Ingresa para cuidar tu memoria</p>
        </div>
          
        <div class="images"><img src="../img/abuelo.png"></div>
        </div>
        <div class="login-box">
        <div><img src="../img/icon.png">
        <h2>Inicia sesión</h2></div>
        <!-- FORMULARIOS -->
        <div id="formCuidador">
          <form>
            <!-- formulario cuidador -->
            <label for="nombreCuidador">Tu nombre:</label>
                <input type="text" id="nombreCuidador" name="nombreCuidador" placeholder="Tu nombre" required>

                <label for="nombreCuidado">Nombre de quien cuidas:</label>
                <input type="text" id="nombreCuidado" name="nombreCuidado" placeholder="Nombre de la persona que cuidas" required>

                <label for="correoCuidador">Correo electrónico:</label>
                <input type="email" id="correoCuidador" name="correoCuidador" placeholder="ejemplo@correo.com" required>

                <label for="contrasenaCuidador">Contraseña:</label>
                <input type="password" id="contrasenaCuidador" name="contrasenaCuidador" placeholder="Contraseña" required>

                <button type="submit">Entrar</button>
          </form>
          <p>¿Aún no tienes cuenta? <a href="registro.php">Crear cuenta</a></p>  
        </div>

        <div class="hidden" id="formAdulto">
          <form method="POST" action="login.php">
            <!-- formulario adulto -->
             <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required>
                    
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="******" required>
                    
                    <div class="options">
                        <label><input type="checkbox" name="recordar"> Recordarme</label>
                        <a href="#">¿Olvidaste la contraseña?</a>
                    </div>
                    
                    <button type="submit">Iniciar sesión</button>
          </form>
          <p>¿Aún no tienes cuenta? <a href="registro.php">Crear cuenta</a></p>
        </div>
        <?php if (!empty($error)): ?>
          <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
      </div>
      </div>
      </div>
    <div class="foot">
        <p>&copy; 2025 Recuerda+ - Todos los derechos reservados</p>
    </div>
</body>
<script>
  const cuidadorBtn = document.getElementById('cuidadorBtn');
  const adultoBtn = document.getElementById('adultoBtn');

  const heroCuidador = document.getElementById('heroCuidador');
  const heroAdulto = document.getElementById('heroAdulto');

  const formCuidador = document.getElementById('formCuidador');
  const formAdulto = document.getElementById('formAdulto');

  cuidadorBtn.addEventListener('click', () => {
    cuidadorBtn.classList.add('active');
    adultoBtn.classList.remove('active');
    heroCuidador.classList.remove('hidden');
    heroAdulto.classList.add('hidden');
    formCuidador.classList.remove('hidden');
    formAdulto.classList.add('hidden');
  });

  adultoBtn.addEventListener('click', () => {
    adultoBtn.classList.add('active');
    cuidadorBtn.classList.remove('active');
    heroCuidador.classList.add('hidden');
    heroAdulto.classList.remove('hidden');
    formCuidador.classList.add('hidden');
    formAdulto.classList.remove('hidden');
  });
</script>

</html>
