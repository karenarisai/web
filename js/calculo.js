// Variables del juego
let score = 0
let currentAnswer = 0

// Mostrar una nueva operación y generar opciones de respuesta
function generateEquation() {
  const num1 = Math.floor(Math.random() * 10) + 1
  const num2 = Math.floor(Math.random() * 10) + 1
  const operator = Math.random() > 0.5 ? "+" : "-"

  const equation = `${num1} ${operator} ${num2}`
  currentAnswer = operator === "+" ? num1 + num2 : num1 - num2

  document.getElementById("equation").textContent = equation

  // Generar opciones de respuesta
  generateOptions()
}

// Generar 3 opciones de respuesta, incluyendo la correcta
function generateOptions() {
  const optionsContainer = document.getElementById("options")
  optionsContainer.innerHTML = "" // Limpiar opciones anteriores

  const correctIndex = Math.floor(Math.random() * 3) // Posición aleatoria para la respuesta correcta
  const options = []

  for (let i = 0; i < 3; i++) {
    if (i === correctIndex) {
      options.push(currentAnswer)
    } else {
      let wrongAnswer
      do {
        wrongAnswer = currentAnswer + (Math.floor(Math.random() * 5) - 2) // Genera respuestas incorrectas cercanas
      } while (wrongAnswer === currentAnswer || options.includes(wrongAnswer))
      options.push(wrongAnswer)
    }
  }

  // Crear botones para las opciones
  options.forEach((answer) => {
    const btn = document.createElement("button")
    btn.textContent = answer
    btn.classList.add("option-btn")
    btn.addEventListener("click", () => checkAnswer(answer))
    optionsContainer.appendChild(btn)
  })
}

// Comprobar la respuesta
function checkAnswer(selectedAnswer) {
  const message = document.createElement("p")
  message.textContent = selectedAnswer === currentAnswer ? "¡Correcto!" : "¡Incorrecto!"
  message.classList.add("feedback-message")

  const optionsContainer = document.getElementById("options")
  optionsContainer.innerHTML = "" // Limpiar opciones
  optionsContainer.appendChild(message)

  if (selectedAnswer === currentAnswer) {
    score++
  }

  document.getElementById("score").textContent = `Puntuación: ${score}`

  setTimeout(() => {
    generateEquation() // Generar nueva operación después de mostrar el mensaje
  }, 1000)

  // Guardar puntuación en el servidor si la puntuación es múltiplo de 5
  if (score > 0 && score % 5 === 0) {
    guardarPuntuacion()
  }
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
        juego: "calculo",
        puntuacion: score,
        detalles: {
          tipo: "basico",
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

// Reiniciar el juego
document.querySelector(".restart-btn").addEventListener("click", () => {
  score = 0
  document.getElementById("score").textContent = `Puntuación: ${score}`
  generateEquation() // Iniciar con una operación
})

// Iniciar el juego
generateEquation()
