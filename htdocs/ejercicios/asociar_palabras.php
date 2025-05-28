<?php
require_once '../config/sesion.php';
require_once '../config/database.php';

// Verificar autenticación
requiereAutenticacion();

// Obtener categorías y palabras de la base de datos
$conexion = conectarDB();

// Crear tabla si no existe
$sql = "CREATE TABLE IF NOT EXISTS asociar_palabras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria VARCHAR(100) NOT NULL,
    palabra VARCHAR(100) NOT NULL,
    UNIQUE KEY (categoria, palabra)
)";
$conexion->query($sql);

// Insertar datos de ejemplo si la tabla está vacía
$resultado = $conexion->query("SELECT COUNT(*) as total FROM asociar_palabras");
$fila = $resultado->fetch_assoc();

if ($fila['total'] == 0) {
    // Categorías y palabras de ejemplo
    $datos = [
        'Frutas' => ['Manzana', 'Plátano', 'Naranja', 'Pera', 'Uva', 'Sandía'],
        'Animales' => ['Perro', 'Gato', 'Elefante', 'León', 'Jirafa', 'Tigre'],
        'Colores' => ['Rojo', 'Azul', 'Verde', 'Amarillo', 'Negro', 'Blanco'],
        'Países' => ['España', 'Francia', 'Italia', 'Alemania', 'Portugal', 'Inglaterra'],
        'Profesiones' => ['Médico', 'Profesor', 'Ingeniero', 'Abogado', 'Arquitecto', 'Enfermero']
    ];
    
    $stmt = $conexion->prepare("INSERT INTO asociar_palabras (categoria, palabra) VALUES (?, ?)");
    
    foreach ($datos as $categoria => $palabras) {
        foreach ($palabras as $palabra) {
            $stmt->bind_param("ss", $categoria, $palabra);
            $stmt->execute();
        }
    }
}

// Obtener categorías
$resultado = $conexion->query("SELECT DISTINCT categoria FROM asociar_palabras ORDER BY categoria");
$categorias = [];
while ($fila = $resultado->fetch_assoc()) {
    $categorias[] = $fila['categoria'];
}

// Obtener palabras para cada categoría
$palabras_por_categoria = [];
foreach ($categorias as $categoria) {
    $stmt = $conexion->prepare("SELECT palabra FROM asociar_palabras WHERE categoria = ? ORDER BY palabra");
    $stmt->bind_param("s", $categoria);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $palabras = [];
    while ($fila = $resultado->fetch_assoc()) {
        $palabras[] = $fila['palabra'];
    }
    
    $palabras_por_categoria[$categoria] = $palabras;
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asociar Palabras - Recuerda+</title>
    <link rel="stylesheet" href="../css/styles.css">
    <script src="../js/menu.js" defer></script>
</head>
<body>
    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="Recuerda+">
            <span>Recuerda+</span>
        </div>
        <nav class="nav">
            <a href="../ejercicios.php" class="nav-link">Ejercicios</a>
            <a href="memorama.php" class="nav-link">Memorama</a>
            <a href="tetris.php" class="nav-link">Tetris</a>
            <a href="adivinanzas.php" class="nav-link">Adivinanzas</a>
            <a href="album.php" class="nav-link">Álbum de fotos</a>
            <a href="calculo.php" class="nav-link">Cálculo rápido</a>
            <a href="../auth/logout.php" id="logout" class="nav-link">Cerrar sesión</a>
        </nav>
        <div class="menu-toggle" id="menu-toggle">☰</div>
    </header>

    <div class="asociar-container">
        <h1>Asociar Palabras</h1>
        <p class="instrucciones">Arrastra las palabras a la categoría correcta.</p>
        
        <div class="asociar-juego">
            <div class="palabras-container">
                <h2>Palabras</h2>
                <div id="palabras" class="palabras">
                    <?php
                    // Mezclar todas las palabras
                    $todas_palabras = [];
                    foreach ($palabras_por_categoria as $palabras) {
                        $todas_palabras = array_merge($todas_palabras, $palabras);
                    }
                    shuffle($todas_palabras);
                    
                    // Mostrar palabras mezcladas
                    foreach ($todas_palabras as $palabra) {
                        echo '<div class="palabra" draggable="true" data-palabra="' . htmlspecialchars($palabra) . '">' . htmlspecialchars($palabra) . '</div>';
                    }
                    ?>
                </div>
            </div>
            
            <div class="categorias-container">
                <?php foreach ($categorias as $categoria): ?>
                <div class="categoria">
                    <h3><?php echo htmlspecialchars($categoria); ?></h3>
                    <div class="categoria-palabras" data-categoria="<?php echo htmlspecialchars($categoria); ?>">
                        <!-- Aquí se colocarán las palabras arrastradas -->
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="asociar-controles">
            <button id="verificar" class="btn-primary">Verificar Respuestas</button>
            <button id="reiniciar" class="btn-secondary">Reiniciar Juego</button>
        </div>
        
        <div id="resultado" class="resultado"></div>
    </div>

    <footer>
        <p>© 2025 Recuerda+ - Todos los derechos reservados</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const palabras = document.querySelectorAll('.palabra');
            const categoriasContenedores = document.querySelectorAll('.categoria-palabras');
            const palabrasContenedor = document.getElementById('palabras');
            const verificarBtn = document.getElementById('verificar');
            const reiniciarBtn = document.getElementById('reiniciar');
            const resultadoDiv = document.getElementById('resultado');
            
            // Datos de categorías y palabras
            const categoriasPalabras = <?php echo json_encode($palabras_por_categoria); ?>;
            
            // Configurar eventos de arrastrar y soltar
            palabras.forEach(palabra => {
                palabra.addEventListener('dragstart', function(e) {
                    e.dataTransfer.setData('text/plain', palabra.dataset.palabra);
                    setTimeout(() => {
                        palabra.classList.add('dragging');
                    }, 0);
                });
                
                palabra.addEventListener('dragend', function() {
                    palabra.classList.remove('dragging');
                });
            });
            
            // Permitir soltar en contenedores de categorías
            categoriasContenedores.forEach(contenedor => {
                contenedor.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    contenedor.classList.add('dragover');
                });
                
                contenedor.addEventListener('dragleave', function() {
                    contenedor.classList.remove('dragover');
                });
                
                contenedor.addEventListener('drop', function(e) {
                    e.preventDefault();
                    contenedor.classList.remove('dragover');
                    
                    const palabraTexto = e.dataTransfer.getData('text/plain');
                    const palabraElement = document.querySelector(`.palabra[data-palabra="${palabraTexto}"]`);
                    
                    if (palabraElement) {
                        contenedor.appendChild(palabraElement);
                    }
                });
            });
            
            // También permitir soltar de vuelta en el contenedor de palabras
            palabrasContenedor.addEventListener('dragover', function(e) {
                e.preventDefault();
                palabrasContenedor.classList.add('dragover');
            });
            
            palabrasContenedor.addEventListener('dragleave', function() {
                palabrasContenedor.classList.remove('dragover');
            });
            
            palabrasContenedor.addEventListener('drop', function(e) {
                e.preventDefault();
                palabrasContenedor.classList.remove('dragover');
                
                const palabraTexto = e.dataTransfer.getData('text/plain');
                const palabraElement = document.querySelector(`.palabra[data-palabra="${palabraTexto}"]`);
                
                if (palabraElement) {
                    palabrasContenedor.appendChild(palabraElement);
                }
            });
            
            // Verificar respuestas
            verificarBtn.addEventListener('click', function() {
                let correctas = 0;
                let incorrectas = 0;
                
                categoriasContenedores.forEach(contenedor => {
                    const categoria = contenedor.dataset.categoria;
                    const palabrasCorrectas = categoriasPalabras[categoria];
                    
                    // Verificar cada palabra en el contenedor
                    const palabrasEnContenedor = contenedor.querySelectorAll('.palabra');
                    palabrasEnContenedor.forEach(palabra => {
                        const palabraTexto = palabra.dataset.palabra;
                        
                        if (palabrasCorrectas.includes(palabraTexto)) {
                            palabra.classList.add('correcta');
                            palabra.classList.remove('incorrecta');
                            correctas++;
                        } else {
                            palabra.classList.add('incorrecta');
                            palabra.classList.remove('correcta');
                            incorrectas++;
                        }
                    });
                });
                
                // Mostrar resultado
                const totalPalabras = document.querySelectorAll('.palabra').length;
                const sinAsignar = totalPalabras - (correctas + incorrectas);
                
                resultadoDiv.innerHTML = `
                    <h3>Resultado:</h3>
                    <p>Palabras correctas: <span class="correctas">${correctas}</span></p>
                    <p>Palabras incorrectas: <span class="incorrectas">${incorrectas}</span></p>
                    <p>Palabras sin asignar: ${sinAsignar}</p>
                `;
                
                if (correctas === totalPalabras) {
                    resultadoDiv.innerHTML += '<p class="felicitacion">¡Felicidades! Has completado el ejercicio correctamente.</p>';
                }
            });
            
            // Reiniciar juego
            reiniciarBtn.addEventListener('click', function() {
                // Devolver todas las palabras al contenedor original
                document.querySelectorAll('.palabra').forEach(palabra => {
                    palabra.classList.remove('correcta', 'incorrecta');
                    palabrasContenedor.appendChild(palabra);
                });
                
                // Limpiar resultado
                resultadoDiv.innerHTML = '';
            });
        });
    </script>
</body>
</html>
