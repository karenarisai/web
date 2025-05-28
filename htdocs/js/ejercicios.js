document.addEventListener("DOMContentLoaded", () => {
  const cards = document.querySelectorAll(".card")
  const jugarBtn = document.querySelector(".jugar-btn")
  const juegoSeleccionado = null

  // Seleccionar juego al hacer clic en una tarjeta
  cards.forEach((card) => {
    card.addEventListener("click", (e) => {
      // No hacemos nada especial aquí ya que los cards son enlaces <a>
      // y ya tienen su propio comportamiento de navegación
    })
  })

  // Actualizar ejercicios realizados (función para uso futuro)
  function actualizarEjerciciosRealizados(juego) {
    const ejerciciosSection = document.querySelector(".ejercicios .cards")

    const nuevaCard = document.createElement("div")
    nuevaCard.classList.add("card", "completado")
    nuevaCard.innerHTML = `
            <img src="img/${juego.toLowerCase().replace(/ /g, "")}.png" alt="${juego}" />
            <h3>${juego}</h3>
            <p>Completado</p>
            <p><strong>Nivel:</strong> Desconocido</p>
            <p>📅 Hoy</p>
        `

    ejerciciosSection.prepend(nuevaCard)
  }
})
