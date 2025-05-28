document.addEventListener("DOMContentLoaded", () => {
  const tablero = document.getElementById("tablero")
  const intentosElement = document.getElementById("intentos")
  const paresElement = document.getElementById("pares")
  const tiempoElement = document.getElementById("tiempo")
  const dificultadSelect = document.getElementById("dificultad")
  const reiniciarBtn = document.getElementById("reiniciar")
  const mensajeVictoria = document.getElementById("mensaje-victoria")
  const tiempoFinalElement = document.getElementById("tiempo-final")
  const intentosFinalElement = document.getElementById("intentos-final")
  const puntuacionFinalElement = document.getElementById("puntuacion-final")
  const jugarOtraVezBtn = document.getElementById("jugar-otra-vez")

  // Configuración del juego
  let cartas = []
  let cartasVolteadas = []
  let paresEncontrados = 0
  let intentos = 0
  let tiempoInicio = null
  let tiempoTranscurrido = 0
  let temporizador = null
  let totalPares = 0
  let juegoIniciado = false
  let juegoTerminado = false

  // Imágenes para las cartas
  const imagenes = [
    "🍎",
    "🍌",
    "🍊",
    "🍇",
    "🍓",
    "🍒",
    "🍑",
    "🍍",
    "🥝",
    "🍅",
    "🥑",
    "🥦",
    "🥕",
    "🌽",
    "🍄",
    "🥜",
    "🐶",
    "🐱",
    "🐭",
    "🐹",
    "🐰",
    "🦊",
    "🐻",
    "🐼",
  ]

  // Configuración según dificultad
  const configuraciones = {
    facil: { filas: 3, columnas: 4 },
    medio: { filas: 4, columnas: 4 },
    dificil: { filas: 4, columnas: 6 },
  }

  // Inicializar juego
  function iniciarJuego() {
    // Detener temporizador anterior si existe
    if (temporizador) {
      clearInterval(temporizador)
    }

    // Reiniciar variables
    cartas = []
    cartasVolteadas = []
    paresEncontrados = 0
    intentos = 0
    tiempoInicio = Date.now()
    tiempoTranscurrido = 0
    juegoIniciado = true
    juegoTerminado = false

    // Actualizar UI
    intentosElement.textContent = intentos
    paresElement.textContent = paresEncontrados
    tiempoElement.textContent = "00:00"
    mensajeVictoria.style.display = "none"

    // Obtener configuración según dificultad
    const dificultad = dificultadSelect.value
    const config = configuraciones[dificultad]
    const filas = config.filas
    const columnas = config.columnas

    // Calcular total de pares
    totalPares = (filas * columnas) / 2

    // Crear array de pares de cartas
    const imagenesSeleccionadas = imagenes.slice(0, totalPares)
    const parejas = [...imagenesSeleccionadas, ...imagenesSeleccionadas]

    // Mezclar cartas
    cartas = parejas.sort(() => Math.random() - 0.5)

    // Crear tablero
    tablero.innerHTML = ""
    tablero.style.gridTemplateColumns = `repeat(${columnas}, 1fr)`

    // Calcular tamaño de las cartas basado en el ancho del tablero
    const cartaSize = Math.min(80, Math.floor(tablero.clientWidth / columnas) - 10)

    // Añadir cartas al tablero
    cartas.forEach((carta, index) => {
      const cartaElement = document.createElement("div")
      cartaElement.className = "carta"
      cartaElement.dataset.index = index
      cartaElement.style.width = `${cartaSize}px`
      cartaElement.style.height = `${cartaSize}px`
      cartaElement.innerHTML = `
                <div class="carta-contenido">
                    <div class="carta-frente"></div>
                    <div class="carta-dorso">${carta}</div>
                </div>
            `
      cartaElement.addEventListener("click", voltearCarta)
      tablero.appendChild(cartaElement)
    })

    // Iniciar temporizador
    temporizador = setInterval(actualizarTiempo, 1000)
  }

  // Voltear carta
  function voltearCarta() {
    // No hacer nada si el juego ha terminado o si ya hay dos cartas volteadas
    if (
      juegoTerminado ||
      cartasVolteadas.length >= 2 ||
      this.classList.contains("volteada") ||
      this.classList.contains("encontrada")
    ) {
      return
    }

    // Voltear carta
    this.classList.add("volteada")
    cartasVolteadas.push(this)

    // Si hay dos cartas volteadas, comprobar si son pareja
    if (cartasVolteadas.length === 2) {
      intentos++
      intentosElement.textContent = intentos

      const index1 = cartasVolteadas[0].dataset.index
      const index2 = cartasVolteadas[1].dataset.index

      // Comprobar si son pareja
      if (cartas[index1] === cartas[index2]) {
        // Son pareja
        paresEncontrados++
        paresElement.textContent = paresEncontrados

        cartasVolteadas.forEach((carta) => {
          carta.classList.add("encontrada")
          carta.classList.remove("volteada")
        })
        cartasVolteadas = []

        // Comprobar si se han encontrado todos los pares
        if (paresEncontrados === totalPares) {
          finalizarJuego()
        }
      } else {
        // No son pareja, voltear de nuevo después de un tiempo
        setTimeout(() => {
          cartasVolteadas.forEach((carta) => {
            carta.classList.remove("volteada")
          })
          cartasVolteadas = []
        }, 1000)
      }
    }
  }

  // Actualizar tiempo
  function actualizarTiempo() {
    if (!juegoIniciado || juegoTerminado) return

    tiempoTranscurrido = Math.floor((Date.now() - tiempoInicio) / 1000)
    const minutos = Math.floor(tiempoTranscurrido / 60)
      .toString()
      .padStart(2, "0")
    const segundos = (tiempoTranscurrido % 60).toString().padStart(2, "0")
    tiempoElement.textContent = `${minutos}:${segundos}`
  }

  // Finalizar juego
  function finalizarJuego() {
    juegoTerminado = true
    clearInterval(temporizador)

    // Calcular puntuación
    const dificultad = dificultadSelect.value
    const puntuacion = calcularPuntuacion(tiempoTranscurrido, intentos, dificultad)

    // Mostrar mensaje de victoria
    const minutos = Math.floor(tiempoTranscurrido / 60)
      .toString()
      .padStart(2, "0")
    const segundos = (tiempoTranscurrido % 60).toString().padStart(2, "0")
    tiempoFinalElement.textContent = `${minutos}:${segundos}`
    intentosFinalElement.textContent = intentos
    puntuacionFinalElement.textContent = puntuacion
    mensajeVictoria.style.display = "block"

    // Guardar puntuación en el servidor
    guardarPuntuacion(tiempoTranscurrido, intentos, puntuacion, dificultad)
  }

  // Guardar puntuación en el servidor
  async function guardarPuntuacion(tiempo, intentos, puntuacion, dificultad) {
    try {
      const response = await fetch("../api/puntuaciones.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          juego: "memorama",
          puntuacion,
          detalles: {
            dificultad,
            tiempo,
            intentos,
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

  // Calcular puntuación
  function calcularPuntuacion(tiempo, intentos, dificultad) {
    const puntuacionBase = 1000
    let factorDificultad = 1

    switch (dificultad) {
      case "facil":
        factorDificultad = 1
        break
      case "medio":
        factorDificultad = 1.5
        break
      case "dificil":
        factorDificultad = 2
        break
    }

    // Penalización por tiempo e intentos
    const penalizacionTiempo = tiempo * 2
    const penalizacionIntentos = intentos * 10

    // Calcular puntuación final
    let puntuacionFinal = (puntuacionBase - penalizacionTiempo - penalizacionIntentos) * factorDificultad

    // Asegurar que la puntuación no sea negativa
    puntuacionFinal = Math.max(0, Math.round(puntuacionFinal))

    return puntuacionFinal
  }

  // Eventos
  reiniciarBtn.addEventListener("click", iniciarJuego)
  jugarOtraVezBtn.addEventListener("click", iniciarJuego)
  dificultadSelect.addEventListener("change", iniciarJuego)

  // Ajustar tamaño del tablero al cambiar el tamaño de la ventana
  window.addEventListener("resize", () => {
    if (juegoIniciado) {
      const dificultad = dificultadSelect.value
      const config = configuraciones[dificultad]
      const columnas = config.columnas

      // Recalcular tamaño de las cartas
      const cartaSize = Math.min(80, Math.floor(tablero.clientWidth / columnas) - 10)

      document.querySelectorAll(".carta").forEach((carta) => {
        carta.style.width = `${cartaSize}px`
        carta.style.height = `${cartaSize}px`
      })
    }
  })

  // Iniciar juego al cargar la página
  iniciarJuego()
})
