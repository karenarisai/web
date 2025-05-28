<?php
require_once 'config/sesion.php';
require_once 'config/database.php';

// Verificar autenticación
requiereAutenticacion();

$usuario_id = obtenerUsuarioId();
$mensaje = '';
$error = '';

// Obtener datos del usuario
$conexion = conectarDB();
$stmt = $conexion->prepare("SELECT nombre, apellidos, email, fecha_nacimiento FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

// Procesar formulario de actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_perfil'])) {
    $nombre = limpiarDatos($_POST['nombre'] ?? '');
    $apellidos = limpiarDatos($_POST['apellidos'] ?? '');
    $email = limpiarDatos($_POST['email'] ?? '');
    $fecha_nacimiento = limpiarDatos($_POST['fecha_nacimiento'] ?? '');
    
    // Validar datos
    if (empty($nombre) || empty($apellidos) || empty($email)) {
        $error = 'Por favor, complete todos los campos obligatorios.';
    } else {
        // Verificar si el email ya existe (si se cambió)
        if ($email !== $usuario['email']) {
            $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $usuario_id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows > 0) {
                $error = 'Este correo electrónico ya está registrado por otro usuario.';
            }
        }
        
        if (empty($error)) {
            // Actualizar datos del usuario
            $stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, apellidos = ?, email = ?, fecha_nacimiento = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $nombre, $apellidos, $email, $fecha_nacimiento, $usuario_id);
            
            if ($stmt->execute()) {
                $mensaje = 'Perfil actualizado correctamente.';
                // Actualizar datos en la sesión
                $_SESSION['usuario_nombre'] = $nombre;
                
                // Actualizar datos locales
                $usuario['nombre'] = $nombre;
                $usuario['apellidos'] = $apellidos;
                $usuario['email'] = $email;
                $usuario['fecha_nacimiento'] = $fecha_nacimiento;
            } else {
                $error = 'Error al actualizar el perfil: ' . $conexion->error;
            }
        }
    }
}

// Verificar si existe la tabla de contactos de emergencia, si no, crearla
$sql = "CREATE TABLE IF NOT EXISTS contactos_emergencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    relacion VARCHAR(50),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
)";
$conexion->query($sql);

// Obtener contactos de emergencia
$stmt = $conexion->prepare("SELECT id, nombre, telefono, relacion FROM contactos_emergencia WHERE usuario_id = ? ORDER BY id");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

$contactos = [];
while ($contacto = $resultado->fetch_assoc()) {
    $contactos[] = $contacto;
}

// Procesar formulario de añadir contacto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_contacto'])) {
    $nombre_contacto = limpiarDatos($_POST['nombre_contacto'] ?? '');
    $telefono_contacto = limpiarDatos($_POST['telefono_contacto'] ?? '');
    $relacion_contacto = limpiarDatos($_POST['relacion_contacto'] ?? '');
    
    if (empty($nombre_contacto) || empty($telefono_contacto)) {
        $error = 'Por favor, complete al menos el nombre y teléfono del contacto.';
    } else {
        $stmt = $conexion->prepare("INSERT INTO contactos_emergencia (usuario_id, nombre, telefono, relacion) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $usuario_id, $nombre_contacto, $telefono_contacto, $relacion_contacto);
        
        if ($stmt->execute()) {
            $mensaje = 'Contacto agregado correctamente.';
            // Recargar la página para mostrar el nuevo contacto
            header('Location: configuracion.php');
            exit;
        } else {
            $error = 'Error al agregar el contacto: ' . $conexion->error;
        }
    }
}

// Procesar eliminación de contacto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_contacto'])) {
    $contacto_id = intval($_POST['contacto_id'] ?? 0);
    
    if ($contacto_id > 0) {
        $stmt = $conexion->prepare("DELETE FROM contactos_emergencia WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $contacto_id, $usuario_id);
        
        if ($stmt->execute()) {
            $mensaje = 'Contacto eliminado correctamente.';
            // Recargar la página para actualizar la lista de contactos
            header('Location: configuracion.php');
            exit;
        } else {
            $error = 'Error al eliminar el contacto: ' . $conexion->error;
        }
    }
}

// Procesar actualización de preferencias
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_preferencias'])) {
    $font_size = intval($_POST['font_size'] ?? 16);
    $high_contrast = isset($_POST['high_contrast']) ? 1 : 0;
    $screen_reader = isset($_POST['screen_reader']) ? 1 : 0;
    $idioma = limpiarDatos($_POST['idioma'] ?? 'es');
    $voz = limpiarDatos($_POST['voz'] ?? 'Maria');
    
    // Verificar si existe la tabla de preferencias, si no, crearla
    $sql = "CREATE TABLE IF NOT EXISTS preferencias_usuario (
        usuario_id INT PRIMARY KEY,
        font_size INT DEFAULT 16,
        high_contrast BOOLEAN DEFAULT FALSE,
        screen_reader BOOLEAN DEFAULT FALSE,
        idioma VARCHAR(10) DEFAULT 'es',
        voz VARCHAR(50) DEFAULT 'Maria',
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )";
    $conexion->query($sql);
    
    // Verificar si el usuario ya tiene preferencias
    $stmt = $conexion->prepare("SELECT usuario_id FROM preferencias_usuario WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        // Actualizar preferencias existentes
        $stmt = $conexion->prepare("UPDATE preferencias_usuario SET font_size = ?, high_contrast = ?, screen_reader = ?, idioma = ?, voz = ? WHERE usuario_id = ?");
        $stmt->bind_param("iiissi", $font_size, $high_contrast, $screen_reader, $idioma, $voz, $usuario_id);
    } else {
        // Insertar nuevas preferencias
        $stmt = $conexion->prepare("INSERT INTO preferencias_usuario (usuario_id, font_size, high_contrast, screen_reader, idioma, voz) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisss", $usuario_id, $font_size, $high_contrast, $screen_reader, $idioma, $voz);
    }
    
    if ($stmt->execute()) {
        $mensaje = 'Preferencias actualizadas correctamente.';
        
        // Guardar preferencias en la sesión para uso inmediato
        $_SESSION['preferencias'] = [
            'font_size' => $font_size,
            'high_contrast' => $high_contrast,
            'screen_reader' => $screen_reader,
            'idioma' => $idioma,
            'voz' => $voz
        ];
    } else {
        $error = 'Error al actualizar las preferencias: ' . $conexion->error;
    }
}

// Obtener preferencias del usuario
$stmt = $conexion->prepare("SELECT * FROM preferencias_usuario WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

$preferencias = [
    'font_size' => 16,
    'high_contrast' => false,
    'screen_reader' => false,
    'idioma' => 'es',
    'voz' => 'Maria'
];

if ($resultado->num_rows > 0) {
    $pref = $resultado->fetch_assoc();
    $preferencias = [
        'font_size' => $pref['font_size'],
        'high_contrast' => $pref['high_contrast'],
        'screen_reader' => $pref['screen_reader'],
        'idioma' => $pref['idioma'],
        'voz' => $pref['voz']
    ];
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Recuerda+</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/configuracion.css">
    <link rel="stylesheet" href="../css/generales.css">
    <script src="js/menu.js" defer></script>
    <script src="js/configuracion.js" defer></script>
    <style>
        body {
            font-size: <?php echo $preferencias['font_size']; ?>px;
        }
        <?php if ($preferencias['high_contrast']): ?>
        body, .container, .section, header, footer {
            background: black;
            color: yellow;
        }
        <?php endif; ?>
    </style>
</head>
<body class="<?php echo $preferencias['high_contrast'] ? 'high-contrast' : ''; ?>">
    <header>
        <div class="logo">
            <img src="img/logo.png" alt="Recuerda+">
            <span>Recuerda+</span>
        </div>
        <nav class="nav">
            <a href="tablero.php" class="nav-link">Inicio</a>
            <a href="agenda.php" class="nav-link">Agenda</a>
            <a href="recordatorios.php" class="nav-link">Recordatorios</a>
            <a href="ejercicios.php" class="nav-link">Ejercicios</a>
            <a href="foro.php" class="nav-link">Foro</a>
            <a href="configuracion.php" class="nav-link active">Configuración</a>
            <a href="auth/logout.php" id="logout" class="nav-link">Cerrar sesión</a>
        </nav>
        <div class="menu-toggle" id="menu-toggle">☰</div>
    </header>
    
    <div class="centro">
        <div class="container">
            <?php if (!empty($mensaje)): ?>
                <div class="success-message"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Perfil de usuario -->
            <div class="profile">
                <img src="img/avatar.png" alt="Usuario">
                <div>
                    <h3><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></h3>
                    <p><i class="fas fa-user"></i> Usuario registrado</p>
                    <button class="edit-btn" id="edit-profile-btn"><i class="fas fa-edit"></i> Editar perfil</button>
                </div>
            </div>
    
            <!-- Accesibilidad -->
            <div class="section">
                <h4><i class="fas fa-universal-access"></i> Accesibilidad</h4>
                <form method="POST" action="configuracion.php" id="form-preferencias">
                    <div class="option">
                        <label for="font-size-slider">Tamaño de fuente: <span id="font-size-value"><?php echo $preferencias['font_size']; ?>px</span></label>
                        <input type="range" min="12" max="24" value="<?php echo $preferencias['font_size']; ?>" id="font-size-slider" name="font_size">
                    </div>
                    <div class="option">
                        Alto contraste
                        <label class="switch">
                            <input type="checkbox" id="contrast-toggle" name="high_contrast" <?php echo $preferencias['high_contrast'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="option">
                        Lector de pantalla
                        <label class="switch">
                            <input type="checkbox" id="screen-reader" name="screen_reader" <?php echo $preferencias['screen_reader'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <input type="hidden" name="actualizar_preferencias" value="1">
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Guardar preferencias</button>
                </form>
            </div>
    
            <!-- Contactos de emergencia -->
            <div class="section">
                <h4><i class="fas fa-phone-alt"></i> Contactos de Emergencia <button class="add-btn" id="add-contact-btn"><i class="fas fa-plus"></i> Agregar</button></h4>
                <?php if (empty($contactos)): ?>
                    <p class="no-contacts">No hay contactos de emergencia registrados.</p>
                <?php else: ?>
                    <?php foreach ($contactos as $contacto): ?>
                        <div class="contact">
                            <img src="img/contact.png" alt="Contacto">
                            <div class="contacto2">
                                <div>
                                    <p><strong><?php echo htmlspecialchars($contacto['nombre']); ?></strong></p>
                                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($contacto['telefono']); ?></p>
                                    <?php if (!empty($contacto['relacion'])): ?>
                                        <small><i class="fas fa-user-friends"></i> <?php echo htmlspecialchars($contacto['relacion']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <form method="POST" action="configuracion.php" class="delete-contact-form">
                                <input type="hidden" name="contacto_id" value="<?php echo $contacto['id']; ?>">
                                <input type="hidden" name="eliminar_contacto" value="1">
                                <button type="submit" class="delete-btn"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
    
            <!-- Idioma y voz -->
            <div class="section">
                <h4><i class="fas fa-language"></i> Idioma y Voz</h4>
                <form method="POST" action="configuracion.php" id="form-idioma">
                    <div class="option">
                        <label for="idioma">Idioma de la aplicación</label>
                        <select id="idioma" name="idioma">
                            <option value="es" <?php echo $preferencias['idioma'] == 'es' ? 'selected' : ''; ?>>Español</option>
                            <option value="en" <?php echo $preferencias['idioma'] == 'en' ? 'selected' : ''; ?>>Inglés</option>
                            <option value="fr" <?php echo $preferencias['idioma'] == 'fr' ? 'selected' : ''; ?>>Francés</option>
                        </select>
                    </div>
                    <div class="option">
                        <label for="voz">Voz del asistente</label>
                        <select id="voz" name="voz">
                            <option value="Maria" <?php echo $preferencias['voz'] == 'Maria' ? 'selected' : ''; ?>>María (Español)</option>
                            <option value="Juan" <?php echo $preferencias['voz'] == 'Juan' ? 'selected' : ''; ?>>Juan (Español)</option>
                            <option value="Emma" <?php echo $preferencias['voz'] == 'Emma' ? 'selected' : ''; ?>>Emma (Inglés)</option>
                        </select>
                    </div>
                    <input type="hidden" name="actualizar_preferencias" value="1">
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Guardar preferencias</button>
                </form>
            </div>

              <!-- Notificaciones -->
<div class="section">
    <h4><i class="fas fa-bell"></i> Notificaciones</h4>
    <form method="POST" action="configuracion.php" id="form-notificaciones">
        <div class="option">
            <label>
                <input type="checkbox" name="notif_recordatorios" <?php echo $preferencias['notif_recordatorios'] ? 'checked' : ''; ?>>
                Recordatorios
            </label>
        </div>
        <div class="option">
            <label>
                <input type="checkbox" name="notif_ejercicios" <?php echo $preferencias['notif_ejercicios'] ? 'checked' : ''; ?>>
                Ejercicios Cognitivos
            </label>
        </div>
        <div class="option">
            <label>
                <input type="checkbox" name="notif_foro" <?php echo $preferencias['notif_foro'] ? 'checked' : ''; ?>>
                Actividad en el Foro
            </label>
        </div>
        <input type="hidden" name="actualizar_notificaciones" value="1">
        <button type="submit" class="btn-save"><i class="fas fa-save"></i> Guardar notificaciones</button>
    </form>
</div>

<!-- Cambiar contraseña -->
<div class="section">
    <h4><i class="fas fa-key"></i> Cambiar Contraseña</h4>
    <form method="POST" action="configuracion.php" id="form-password">
        <div class="form-group">
            <label for="password_actual"><i class="fas fa-lock"></i> Contraseña Actual</label>
            <input type="password" id="password_actual" name="password_actual" required>
        </div>
        <div class="form-group">
            <label for="nueva_password"><i class="fas fa-lock"></i> Nueva Contraseña</label>
            <input type="password" id="nueva_password" name="nueva_password" required>
        </div>
        <div class="form-group">
            <label for="confirmar_password"><i class="fas fa-lock"></i> Confirmar Nueva Contraseña</label>
            <input type="password" id="confirmar_password" name="confirmar_password" required>
        </div>
        <input type="hidden" name="cambiar_contraseña" value="1">
        <button type="submit" class="btn-save"><i class="fas fa-save"></i> Guardar nueva contraseña</button>
    </form>
</div>
        </div>
        
    </div>

  

    
    <!-- Modal para editar perfil -->
    <div id="edit-profile-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2><i class="fas fa-user-edit"></i> Editar Perfil</h2>
            <form method="POST" action="configuracion.php">
                <div class="form-group">
                    <label for="nombre"><i class="fas fa-user"></i> Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="apellidos"><i class="fas fa-user"></i> Apellidos</label>
                    <input type="text" id="apellidos" name="apellidos" value="<?php echo htmlspecialchars($usuario['apellidos'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Correo Electrónico</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="fecha_nacimiento"><i class="fas fa-calendar-alt"></i> Fecha de Nacimiento</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($usuario['fecha_nacimiento'] ?? ''); ?>">
                </div>
                
                <input type="hidden" name="actualizar_perfil" value="1">
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar Cambios</button>
            </form>
        </div>
    </div>

    <!-- Modal para agregar contacto -->
    <div id="add-contact-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-modal" id="close-contact-modal">&times;</span>
            <h2><i class="fas fa-user-plus"></i> Agregar Contacto de Emergencia</h2>
            <form method="POST" action="configuracion.php">
                <div class="form-group">
                    <label for="nombre_contacto"><i class="fas fa-user"></i> Nombre</label>
                    <input type="text" id="nombre_contacto" name="nombre_contacto" required>
                </div>
                
                <div class="form-group">
                    <label for="telefono_contacto"><i class="fas fa-phone"></i> Teléfono</label>
                    <input type="tel" id="telefono_contacto" name="telefono_contacto" required>
                </div>
                
                <div class="form-group">
                    <label for="relacion_contacto"><i class="fas fa-user-friends"></i> Relación</label>
                    <input type="text" id="relacion_contacto" name="relacion_contacto" placeholder="Ej: Familiar, Médico, Vecino">
                </div>
                
                <input type="hidden" name="agregar_contacto" value="1">
                <button type="submit" class="btn-primary"><i class="fas fa-plus"></i> Agregar Contacto</button>
            </form>
        </div>
    </div>

    <div class="foot">
        © 2025 Recuerda+ - Todos los derechos reservados
    </div>
</body>
</html>
