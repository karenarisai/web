document.addEventListener("DOMContentLoaded", () => {
  const board = document.getElementById("tetris-board")
  const nextPieceDisplay = document.getElementById("next-piece")
  const scoreDisplay = document.getElementById("score")
  const levelDisplay = document.getElementById("level")
  const linesDisplay = document.getElementById("lines")
  const startButton = document.getElementById("start-game")
  const restartButton = document.getElementById("restart-game")
  const gameOverDisplay = document.getElementById("game-over")
  const finalScoreDisplay = document.getElementById("final-score")

  // Configuración del juego
  const ROWS = 20
  const COLS = 10
  const BLOCK_SIZE = 30
  const NEXT_BLOCK_SIZE = 30
  const EMPTY = "white"

  // Piezas del Tetris
  const PIECES = [
    { shape: [[1, 1, 1, 1]], color: "tetris-piece-I", name: "I" }, // I
    {
      shape: [
        [1, 1],
        [1, 1],
      ],
      color: "tetris-piece-O",
      name: "O",
    }, // O
    {
      shape: [
        [0, 1, 0],
        [1, 1, 1],
      ],
      color: "tetris-piece-T",
      name: "T",
    }, // T
    {
      shape: [
        [0, 1, 1],
        [1, 1, 0],
      ],
      color: "tetris-piece-S",
      name: "S",
    }, // S
    {
      shape: [
        [1, 1, 0],
        [0, 1, 1],
      ],
      color: "tetris-piece-Z",
      name: "Z",
    }, // Z
    {
      shape: [
        [1, 0, 0],
        [1, 1, 1],
      ],
      color: "tetris-piece-J",
      name: "J",
    }, // J
    {
      shape: [
        [0, 0, 1],
        [1, 1, 1],
      ],
      color: "tetris-piece-L",
      name: "L",
    }, // L
  ]

  // Variables del juego
  const grid = []
  let currentPiece = null
  let nextPiece = null
  let score = 0
  let level = 1
  let lines = 0
  let gameInterval = null
  let isPaused = false
  let gameStarted = false
  let gameOver = false

  // Inicializar tablero
  function initBoard() {
    board.innerHTML = ""
    board.style.gridTemplateRows = `repeat(${ROWS}, 1fr)`
    board.style.gridTemplateColumns = `repeat(${COLS}, 1fr)`

    // Crear celdas del tablero
    for (let row = 0; row < ROWS; row++) {
      grid[row] = []
      for (let col = 0; col < COLS; col++) {
        const cell = document.createElement("div")
        cell.className = "tetris-cell"
        cell.style.backgroundColor = EMPTY
        board.appendChild(cell)
        grid[row][col] = { cell, color: EMPTY }
      }
    }

    // Inicializar display de siguiente pieza
    nextPieceDisplay.innerHTML = ""
    nextPieceDisplay.style.gridTemplateRows = `repeat(4, 1fr)`
    nextPieceDisplay.style.gridTemplateColumns = `repeat(4, 1fr)`

    for (let row = 0; row < 4; row++) {
      for (let col = 0; col < 4; col++) {
        const cell = document.createElement("div")
        cell.className = "tetris-cell"
        cell.style.backgroundColor = EMPTY
        nextPieceDisplay.appendChild(cell)
      }
    }
  }

  // Generar pieza aleatoria
  function getRandomPiece() {
    const randomIndex = Math.floor(Math.random() * PIECES.length)
    const piece = PIECES[randomIndex]
    return {
      shape: piece.shape,
      color: piece.color,
      name: piece.name,
      row: 0,
      col: Math.floor(COLS / 2) - Math.floor(piece.shape[0].length / 2),
    }
  }

  // Dibujar pieza en el tablero
  function drawPiece() {
    // Limpiar tablero de la pieza anterior
    clearPiece()

    // Dibujar nueva posición
    for (let row = 0; row < currentPiece.shape.length; row++) {
      for (let col = 0; col < currentPiece.shape[row].length; col++) {
        if (currentPiece.shape[row][col]) {
          const boardRow = currentPiece.row + row
          const boardCol = currentPiece.col + col
          if (boardRow >= 0 && boardRow < ROWS && boardCol >= 0 && boardCol < COLS) {
            grid[boardRow][boardCol].cell.style.backgroundColor = ""
            grid[boardRow][boardCol].cell.className = `tetris-cell ${currentPiece.color}`
          }
        }
      }
    }
  }

  // Limpiar pieza del tablero
  function clearPiece() {
    for (let row = 0; row < ROWS; row++) {
      for (let col = 0; col < COLS; col++) {
        if (grid[row][col].color === EMPTY) {
          grid[row][col].cell.style.backgroundColor = EMPTY
          grid[row][col].cell.className = "tetris-cell"
        }
      }
    }
  }

  // Mostrar siguiente pieza
  function showNextPiece() {
    // Limpiar display
    const cells = nextPieceDisplay.querySelectorAll(".tetris-cell")
    cells.forEach((cell) => {
      cell.style.backgroundColor = EMPTY
      cell.className = "tetris-cell"
    })

    // Centrar pieza en el display
    const offsetRow = Math.floor((4 - nextPiece.shape.length) / 2)
    const offsetCol = Math.floor((4 - nextPiece.shape[0].length) / 2)

    // Dibujar siguiente pieza
    for (let row = 0; row < nextPiece.shape.length; row++) {
      for (let col = 0; col < nextPiece.shape[row].length; col++) {
        if (nextPiece.shape[row][col]) {
          const displayRow = offsetRow + row
          const displayCol = offsetCol + col
          const index = displayRow * 4 + displayCol
          cells[index].style.backgroundColor = ""
          cells[index].className = `tetris-cell ${nextPiece.color}`
        }
      }
    }
  }

  // Comprobar colisión
  function checkCollision(piece, rowOffset = 0, colOffset = 0) {
    for (let row = 0; row < piece.shape.length; row++) {
      for (let col = 0; col < piece.shape[row].length; col++) {
        if (piece.shape[row][col]) {
          const newRow = piece.row + row + rowOffset
          const newCol = piece.col + col + colOffset

          // Comprobar límites del tablero
          if (newRow >= ROWS || newCol < 0 || newCol >= COLS) {
            return true
          }

          // Comprobar colisión con piezas existentes
          if (newRow >= 0 && grid[newRow][newCol].color !== EMPTY) {
            return true
          }
        }
      }
    }
    return false
  }

  // Mover pieza
  function movePiece(rowOffset, colOffset) {
    if (gameOver || isPaused || !gameStarted) return

    if (!checkCollision(currentPiece, rowOffset, colOffset)) {
      currentPiece.row += rowOffset
      currentPiece.col += colOffset
      drawPiece()
      return true
    }
    return false
  }

  // Rotar pieza
  function rotatePiece() {
    if (gameOver || isPaused || !gameStarted) return

    // Clonar pieza actual
    const originalShape = currentPiece.shape
    const originalRow = currentPiece.row
    const originalCol = currentPiece.col

    // Crear matriz para la forma rotada
    const rotatedShape = []
    for (let col = 0; col < originalShape[0].length; col++) {
      const newRow = []
      for (let row = originalShape.length - 1; row >= 0; row--) {
        newRow.push(originalShape[row][col])
      }
      rotatedShape.push(newRow)
    }

    // Aplicar rotación
    currentPiece.shape = rotatedShape

    // Comprobar si la rotación es válida
    if (checkCollision(currentPiece)) {
      // Si hay colisión, intentar ajustar la posición
      const kicks = [-1, 1, -2, 2] // Intentar mover izquierda, derecha, más izquierda, más derecha
      let validRotation = false

      for (const kick of kicks) {
        currentPiece.col += kick
        if (!checkCollision(currentPiece)) {
          validRotation = true
          break
        }
        currentPiece.col -= kick
      }

      // Si no se puede rotar, restaurar forma original
      if (!validRotation) {
        currentPiece.shape = originalShape
        currentPiece.row = originalRow
        currentPiece.col = originalCol
      }
    }

    drawPiece()
  }

  // Fijar pieza en el tablero
  function lockPiece() {
    for (let row = 0; row < currentPiece.shape.length; row++) {
      for (let col = 0; col < currentPiece.shape[row].length; col++) {
        if (currentPiece.shape[row][col]) {
          const boardRow = currentPiece.row + row
          const boardCol = currentPiece.col + col

          // Si la pieza está fuera del tablero superior, game over
          if (boardRow < 0) {
            endGame()
            return
          }

          grid[boardRow][boardCol].color = currentPiece.color
          grid[boardRow][boardCol].cell.className = `tetris-cell ${currentPiece.color}`
        }
      }
    }

    // Comprobar líneas completas
    checkLines()

    // Siguiente pieza
    currentPiece = nextPiece
    nextPiece = getRandomPiece()
    showNextPiece()

    // Comprobar si la nueva pieza puede entrar en el tablero
    if (checkCollision(currentPiece)) {
      endGame()
    } else {
      drawPiece()
    }
  }

  // Comprobar líneas completas
  function checkLines() {
    let linesCleared = 0

    for (let row = ROWS - 1; row >= 0; row--) {
      let isLineComplete = true

      for (let col = 0; col < COLS; col++) {
        if (grid[row][col].color === EMPTY) {
          isLineComplete = false
          break
        }
      }

      if (isLineComplete) {
        // Eliminar línea
        for (let r = row; r > 0; r--) {
          for (let col = 0; col < COLS; col++) {
            grid[r][col].color = grid[r - 1][col].color
            grid[r][col].cell.className = grid[r - 1][col].cell.className
          }
        }

        // Limpiar primera línea
        for (let col = 0; col < COLS; col++) {
          grid[0][col].color = EMPTY
          grid[0][col].cell.className = "tetris-cell"
          grid[0][col].cell.style.backgroundColor = EMPTY
        }

        linesCleared++
        row++ // Volver a comprobar la misma fila
      }
    }

    // Actualizar puntuación
    if (linesCleared > 0) {
      lines += linesCleared
      linesDisplay.textContent = lines

      // Calcular puntuación según número de líneas eliminadas
      const points = [0, 100, 300, 500, 800]
      score += points[linesCleared] * level
      scoreDisplay.textContent = score

      // Subir de nivel cada 10 líneas
      const newLevel = Math.floor(lines / 10) + 1
      if (newLevel > level) {
        level = newLevel
        levelDisplay.textContent = level
        // Aumentar velocidad
        clearInterval(gameInterval)
        gameInterval = setInterval(moveDown, Math.max(100, 1000 - (level - 1) * 100))
      }
    }
  }

  // Mover pieza hacia abajo
  function moveDown() {
    if (!movePiece(1, 0)) {
      lockPiece()
    }
  }

  // Caída instantánea
  function hardDrop() {
    while (movePiece(1, 0)) {
      // Seguir moviendo hacia abajo hasta colisionar
    }
    lockPiece()
  }

  // Iniciar juego
  function startGame() {
    if (gameStarted) return

    // Reiniciar variables
    score = 0
    level = 1
    lines = 0
    gameOver = false
    isPaused = false
    gameStarted = true

    // Actualizar UI
    scoreDisplay.textContent = score
    levelDisplay.textContent = level
    linesDisplay.textContent = lines
    gameOverDisplay.style.display = "none"

    // Inicializar tablero
    initBoard()

    // Generar piezas iniciales
    currentPiece = getRandomPiece()
    nextPiece = getRandomPiece()
    showNextPiece()
    drawPiece()

    // Iniciar intervalo
    gameInterval = setInterval(moveDown, 1000)

    // Cambiar texto del botón
    startButton.textContent = "Pausar Juego"
  }

  // Pausar/Reanudar juego
  function togglePause() {
    if (!gameStarted || gameOver) return

    isPaused = !isPaused

    if (isPaused) {
      clearInterval(gameInterval)
      startButton.textContent = "Reanudar Juego"
    } else {
      gameInterval = setInterval(moveDown, Math.max(100, 1000 - (level - 1) * 100))
      startButton.textContent = "Pausar Juego"
    }
  }

  // Finalizar juego
  function endGame() {
    gameOver = true
    gameStarted = false
    clearInterval(gameInterval)
    finalScoreDisplay.textContent = score
    gameOverDisplay.style.display = "block"
    startButton.textContent = "Iniciar Juego"

    // Guardar puntuación en el servidor
    guardarPuntuacion()
  }

  // Guardar puntuación en el servidor
  async function guardarPuntuacion() {
    try {
      const response = await fetch("../api/puntuaciones.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          juego: "tetris",
          puntuacion: score,
          detalles: {
            level,
            lines,
          },
        }),
      })

      if (!response.ok) {
        throw new Error("Error al guardar puntuación")
      }
    } catch (error) {
      console.error("Error:", error)
    }
  }

  // Eventos
  startButton.addEventListener("click", () => {
    if (!gameStarted) {
      startGame()
    } else {
      togglePause()
    }
  })

  restartButton.addEventListener("click", startGame)

  // Controles de teclado
  document.addEventListener("keydown", (e) => {
    if (!gameStarted || gameOver) return

    if (!isPaused) {
      switch (e.key) {
        case "ArrowLeft":
          movePiece(0, -1)
          break
        case "ArrowRight":
          movePiece(0, 1)
          break
        case "ArrowDown":
          moveDown()
          break
        case "ArrowUp":
          rotatePiece()
          break
        case " ":
          hardDrop()
          break
      }
    }

    if (e.key === "p" || e.key === "P") {
      togglePause()
    }
  })

  // Inicializar tablero
  initBoard()
})
