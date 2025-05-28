<?php
require_once 'config/sesion.php';
require_once 'config/database.php';

// Verificar autenticación
requiereAutenticacion();

$usuario_id = obtenerUsuarioId();

$conexion = conectarDB();

$stmt = $conexion->prepare("
    SELECT id, titulo, fecha, hora, descripcion, completado, tipo
    FROM eventos 
    WHERE usuario_id = ? 
    ORDER BY fecha, hora
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

$recordatorios = [];
while ($recordatorio = $resultado->fetch_assoc()) {
    $recordatorios[] = $recordatorio;
}

$stmt->close();
$conexion->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordatorios - Recuerda+</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
     <link rel="stylesheet" href="css/generales.css">
    <script src="js/menu.js" defer></script>
    <script src="js/recordatorios.js" defer></script>
</head>
<body>
    <header>
        <div class="logo">
            <img src="img/logo.png" alt="Recuerda+">
            <span>Recuerda+</span>
        </div>
        <nav class="nav">
            <a href="tablero.php" class="nav-link">Inicio</a>
            <a href="agenda.php" class="nav-link">Agenda</a>
            <a href="recordatorios.php" class="nav-link active">Recordatorios</a>
            <a href="ejercicios.php" class="nav-link">Ejercicios</a>
            <a href="foro.php" class="nav-link">Foro</a>
            <a href="configuracion.php" class="nav-link">Configuración</a>
            <a href="auth/logout.php" id="logout" class="nav-link">Cerrar sesión</a>
        </nav>
        <div class="menu-toggle" id="menu-toggle">☰</div>
    </header>

    <section class="hero">
        <div class="left-img">
            <img src="img/abuelo2.png" alt="Ilustración izquierda">
        </div>
        <div class="text">
            <h1>Mis Recordatorios</h1>
            <p>Organiza tus actividades y medicamentos</p>
        </div>
        <div class="right-img">
            <img src="img/image 1.png" alt="Ilustración de abuelo">
        </div>
    </section>

    <div class="container">
    <div class="recordatorios-container">
        <div class="recordatorios-header">
            <h2>Mis Recordatorios</h2>
            <div class="recordatorios-filtros">
                <button class="filtro-btn active" data-filtro="todos">Todos</button>
                <button class="filtro-btn" data-filtro="medicamentos">Medicamentos</button>
                <button class="filtro-btn" data-filtro="citas">Citas Médicas</button>
                <button class="filtro-btn" data-filtro="completados">Completados</button>
            </div>
        </div>

        <div class="recordatorios-lista">
            <?php if (empty($recordatorios)): ?>
                <div class="no-recordatorios">
                    <p>No tienes recordatorios. ¡Añade uno nuevo!</p>
                </div>
            <?php else: ?>
                <?php foreach ($recordatorios as $recordatorio): ?>
                    <div class="recordatorio-item <?php 
                        echo ($recordatorio['completado'] ? 'completado' : ($recordatorio['tipo'] == 'medicamento' ? 'medicamento' : 'cita')); ?>" 
                        data-id="<?php echo $recordatorio['id']; ?>" 
                        data-tipo="<?php echo htmlspecialchars($recordatorio['tipo'] ?? 'otro'); ?>">
                        
                        <div class="recordatorio-check">
                            <input type="checkbox" <?php echo $recordatorio['completado'] ? 'checked' : ''; ?>>
                        </div>
                        <div class="recordatorio-info">
                            <h3><?php echo htmlspecialchars($recordatorio['titulo']); ?></h3>
                            <div class="recordatorio-meta">
                                <span class="recordatorio-fecha">
                                    <?php 
                                        $fecha = new DateTime($recordatorio['fecha']);
                                        echo $fecha->format('d/m/Y'); 
                                    ?>
                                </span>
                                <span class="recordatorio-hora"><?php echo htmlspecialchars($recordatorio['hora']); ?></span>
                                <span class="recordatorio-tipo"><?php echo htmlspecialchars($recordatorio['tipo'] ?? 'Otro'); ?></span>
                            </div>
                            <?php if (!empty($recordatorio['descripcion'])): ?>
                                <p class="recordatorio-descripcion"><?php echo htmlspecialchars($recordatorio['descripcion']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="recordatorio-acciones">
                            <button class="editar-btn" data-id="<?php echo $recordatorio['id']; ?>"><i class="fas fa-edit"></i></button>
                            <button class="eliminar-btn" data-id="<?php echo $recordatorio['id']; ?>"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="recordatorios-botones">
            <button id="nuevo-medicamento" class="btn-medicamento"><i class="fas fa-pills"></i> Nuevo Medicamento</button>
            <button id="nueva-cita" class="btn-cita"><i class="fas fa-calendar-check"></i> Nueva Cita Médica</button>
        </div>
    </div>

    <div id="modal-recordatorio" class="modal">
        <div class="modal-contenido">
            <span class="cerrar-modal">&times;</span>
            <h2 id="modal-titulo">Nuevo Recordatorio</h2>
            <form id="form-recordatorio">
                <input type="hidden" id="recordatorio-id">
                <input type="hidden" id="recordatorio-tipo" value="otro">
                <div class="form-group">
                    <label for="titulo">Título</label>
                    <input type="text" id="titulo" required>
                </div>
                <div class="form-group">
                    <label for="fecha">Fecha</label>
                    <input type="date" id="fecha" required>
                </div>
                <div class="form-group">
                    <label for="hora">Hora</label>
                    <input type="time" id="hora" required>
                </div>
                <div class="form-group medicamento-fields" style="display: none;">
                    <label for="dosis">Dosis</label>
                    <input type="text" id="dosis" placeholder="Ej: 1 pastilla">
                </div>
                <div class="form-group medicamento-fields" style="display: none;">
                    <label for="frecuencia">Frecuencia</label>
                    <select id="frecuencia">
                        <option value="diaria">Diaria</option>
                        <option value="cada12h">Cada 12 horas</option>
                        <option value="cada8h">Cada 8 horas</option>
                        <option value="semanal">Semanal</option>
                    </select>
                </div>
                <div class="form-group cita-fields" style="display: none;">
                    <label for="doctor">Doctor/Especialista</label>
                    <input type="text" id="doctor" placeholder="Ej: Dr. García - Cardiólogo">
                </div>
                <div class="form-group cita-fields" style="display: none;">
                    <label for="lugar">Lugar</label>
                    <input type="text" id="lugar" placeholder="Ej: Hospital Central - Consulta 5">
                </div>
                <div class="form-group">
                    <label for="descripcion">Notas adicionales</label>
                    <textarea id="descripcion"></textarea>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="completado">
                        Marcar como completado
                    </label>
                </div>
                <button type="submit" class="btn-primary">Guardar</button>
            </form>
        </div>
    </div>
</div>

    <div class="foot">
        <p>© 2025 Recuerda+ - Todos los derechos reservados</p>
    </div>
</body>
</html>
