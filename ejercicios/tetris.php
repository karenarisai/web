<?php
require_once '../config/sesion.php';

// Verificar autenticación
requiereAutenticacion();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tetris - Recuerda+</title>
    <link rel="stylesheet" href="../css/styles.css">
    <script src="../js/menu.js" defer></script>
    <script src="../js/tetris.js" defer></script>
    <style>
        .tetris-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .tetris-game {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .tetris-board {
            width: 300px;
            height: 600px;
            border: 2px solid #333;
            background-color: #f0f0f0;
            display: grid;
            grid-template-rows: repeat(20, 1fr);
            grid-template-columns: repeat(10, 1fr);
        }
        
        .tetris-info {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .next-piece {
            width: 120px;
            height: 120px;
            border: 2px solid #333;
            background-color: #f0f0f0;
            display: grid;
            grid-template-rows: repeat(4, 1fr);
            grid-template-columns: repeat(4, 1fr);
        }
        
        .tetris-stats {
            text-align: left;
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
        }
        
        .tetris-controls {
            text-align: left;
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
        }
        
        .tetris-cell {
            border: 1px solid #ddd;
        }
        
        .tetris-piece-I { background-color: #00f0f0; }
        .tetris-piece-O { background-color: #f0f000; }
        .tetris-piece-T { background-color: #a000f0; }
        .tetris-piece-S { background-color: #00f000; }
        .tetris-piece-Z { background-color: #f00000; }
        .tetris-piece-J { background-color: #0000f0; }
        .tetris-piece-L { background-color: #f0a000; }
        
        .game-over {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            z-index: 100;
        }
        
        @media (max-width: 768px) {
            .tetris-game {
                flex-direction: column;
                align-items: center;
            }
            
            .tetris-board {
                width: 250px;
                height: 500px;
            }
        }
    </style>
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
            <a href="tetris.php" class="nav-link active">Tetris</a>
            <a href="adivinanzas.php" class="nav-link">Adivinanzas</a>
            <a href="album.php" class="nav-link">Álbum de fotos</a>
            <a href="calculo.php" class="nav-link">Cálculo rápido</a>
            <a href="../auth/logout.php" id="logout" class="nav-link">Cerrar sesión</a>
        </nav>
        <div class="menu-toggle" id="menu-toggle">☰</div>
    </header>

    <div class="tetris-container">
        <h1>Tetris</h1>
        <p>Mejora tu coordinación y pensamiento espacial con este clásico juego.</p>
        
        <div class="tetris-game">
            <div class="tetris-board-container">
                <div id="tetris-board" class="tetris-board"></div>
                <div id="game-over" class="game-over" style="display: none;">
                    <h2>¡Juego Terminado!</h2>
                    <p>Tu puntuación final: <span id="final-score">0</span></p>
                    <button id="restart-game" class="btn-primary">Jugar de nuevo</button>
                </div>
            </div>
            
            <div class="tetris-info">
                <div>
                    <h3>Siguiente pieza</h3>
                    <div id="next-piece" class="next-piece"></div>
                </div>
                
                <div class="tetris-stats">
                    <h3>Estadísticas</h3>
                    <p>Puntuación: <span id="score">0</span></p>
                    <p>Nivel: <span id="level">1</span></p>
                    <p>Líneas: <span id="lines">0</span></p>
                </div>
                
                <div class="tetris-controls">
                    <h3>Controles</h3>
                    <p>← → : Mover izquierda/derecha</p>
                    <p>↑ : Rotar pieza</p>
                    <p>↓ : Bajar más rápido</p>
                    <p>Espacio : Caída instantánea</p>
                    <p>P : Pausar juego</p>
                </div>
                
                <button id="start-game" class="btn-primary">Iniciar Juego</button>
            </div>
        </div>
    </div>

    <footer>
        <p>© 2025 Recuerda+ - Todos los derechos reservados</p>
    </footer>
</body>
</html>
