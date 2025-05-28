const adivinanzas = [
  {
    pregunta: "Blanca por dentro, verde por fuera. Si quieres que te lo diga, espera.",
    respuesta: "Pera",
    opciones: ["Manzana", "Pera", "Sandía"],
  },
  { pregunta: "Oro parece, plata no es. ¿Qué es?", respuesta: "Plátano", opciones: ["Plátano", "Oro", "Queso"] },
  {
    pregunta: "Tengo hojas pero no soy árbol, y si me usas es para estudiar.",
    respuesta: "Libro",
    opciones: ["Árbol", "Cuaderno", "Libro"],
  },
  {
    pregunta: "Vuelo de noche, duermo en el día y nunca verás plumas en ala mía.",
    respuesta: "Murciélago",
    opciones: ["Lechuza", "Murciélago", "Águila"],
  },
  {
    pregunta: "Me rascan sin tener comezón y escribo sin tener mano.",
    respuesta: "Lápiz",
    opciones: ["Papel", "Lápiz", "Borrador"],
  },
  {
    pregunta: "¿Qué tiene llaves pero no abre puertas?",
    respuesta: "Piano",
    opciones: ["Cerradura", "Llavero", "Piano"],
  },
  {
    pregunta: "¿Qué tiene dientes pero no puede masticar?",
    respuesta: "Peine",
    opciones: ["Peine", "Tenedor", "Sierra"],
  },
  { pregunta: "¿Qué tiene agujas pero no puede coser?", respuesta: "Reloj", opciones: ["Cactus", "Reloj", "Erizo"] },
]

let adivinanzaActual = {}
let puntuacion = 0

function nuevaAdivinanza() {
  adivinanzaActual = adivinanzas[Math.floor(Math.random() * adivinanzas.length)]
  document.getElementById("pregunta").textContent = adivinanzaActual.pregunta
  document.getElementById("mensaje").textContent = ""

  // Crear botones de opciones
  const opcionesDiv = document.getElementById("opciones")
  opcionesDiv.innerHTML = "" // Limpiar opciones anteriores

  // Mezclar opciones
  const opcionesMezcladas = [...adivinanzaActual.opciones]
  for (let i = opcionesMezcladas.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[opcionesMezcladas[i], opcionesMezcladas[j]] = [opcionesMezcladas[j], opcionesMezcladas[i]]
  }

  opcionesMezcladas.forEach((opcion) => {
    const boton = document.createElement("button")
    boton.textContent = opcion
    boton.addEventListener("click", () => verificarRespuesta(opcion))
    opcionesDiv.appendChild(boton)
  })
}

function verificarRespuesta(opcionSeleccionada) {
  const mensaje = document.getElementById("mensaje")
  const esCorrecta = opcionSeleccionada === adivinanzaActual.respuesta

  if (esCorrecta) {
    mensaje.textContent = "¡Correcto! 🎉"
    mensaje.style.color = "green"
    puntuacion += 10

    // Guardar puntuación cada 3 aciertos
    if (puntuacion % 30 === 0) {
      guardarPuntuacion()
    }
  } else {
    mensaje.textContent = `Incorrecto. La respuesta correcta era: ${adivinanzaActual.respuesta}`
    mensaje.style.color = "red"
  }

  // Deshabilitar botones después de responder
  const botones = document.querySelectorAll("#opciones button")
  botones.forEach((boton) => {
    boton.disabled = true
    if (boton.textContent === adivinanzaActual.respuesta) {
      boton.style.backgroundColor = "#4CAF50"
      boton.style.color = "white"
    } else if (boton.textContent === opcionSeleccionada && !esCorrecta) {
      boton.style.backgroundColor = "#f44336"
      boton.style.color = "white"
    }
  })
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
        juego: "adivinanzas",
        puntuacion: puntuacion,
        detalles: {
          tipo: "general",
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

document.getElementById("nueva").addEventListener("click", nuevaAdivinanza)

// Cargar la primera adivinanza al iniciar
document.addEventListener("DOMContentLoaded", () => {
  nuevaAdivinanza()
})
