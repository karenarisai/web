document.addEventListener("DOMContentLoaded", () => {
  const calendar = document.getElementById("calendar")
  const eventList = document.getElementById("event-list")
  const form = document.getElementById("event-form")
  const dateInput = document.getElementById("fecha")
  const prevMonthBtn = document.getElementById("prev-month")
  const nextMonthBtn = document.getElementById("next-month")
  const currentMonthLabel = document.getElementById("current-month")
  const selectedDateLabel = document.getElementById("selected-date")

  const today = new Date()
  let currentYear = today.getFullYear()
  let currentMonth = today.getMonth()
  let selectedDate = today.toISOString().split("T")[0] // Formato YYYY-MM-DD

  // Función para cargar eventos desde el servidor
  async function cargarEventos(fecha) {
    try {
      const response = await fetch(`api/eventos.php?fecha=${fecha}`)
      if (!response.ok) {
        throw new Error("Error al cargar eventos")
      }
      return await response.json()
    } catch (error) {
      console.error("Error:", error)
      return []
    }
  }

  // Función para guardar un evento en el servidor
  async function guardarEvento(evento) {
    try {
      const response = await fetch("api/eventos.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(evento),
      })

      if (!response.ok) {
        throw new Error("Error al guardar evento")
      }

      return await response.json()
    } catch (error) {
      console.error("Error:", error)
      return null
    }
  }

  // Función para eliminar un evento del servidor
  async function eliminarEvento(id) {
    try {
      const response = await fetch(`api/eventos.php?id=${id}`, {
        method: "DELETE",
      })

      if (!response.ok) {
        throw new Error("Error al eliminar evento")
      }

      return await response.json()
    } catch (error) {
      console.error("Error:", error)
      return null
    }
  }

  // Función para actualizar un evento en el servidor
  async function actualizarEvento(id, datos) {
    try {
      const response = await fetch(`api/eventos.php?id=${id}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(datos),
      })

      if (!response.ok) {
        throw new Error("Error al actualizar evento")
      }

      return await response.json()
    } catch (error) {
      console.error("Error:", error)
      return null
    }
  }

  // Función para actualizar el calendario
  function actualizarCalendario() {
    calendar.innerHTML = ""
    currentMonthLabel.textContent = new Date(currentYear, currentMonth).toLocaleString("es-ES", {
      month: "long",
      year: "numeric",
    })

    const firstDay = new Date(currentYear, currentMonth, 1).getDay()
    const totalDays = new Date(currentYear, currentMonth + 1, 0).getDate()

    // Ajustar para que la semana comience en lunes (0 = lunes, 6 = domingo)
    const adjustedFirstDay = firstDay === 0 ? 6 : firstDay - 1

    for (let i = 0; i < adjustedFirstDay; i++) {
      calendar.appendChild(document.createElement("div"))
    }

    for (let day = 1; day <= totalDays; day++) {
      const dateStr = `${currentYear}-${(currentMonth + 1).toString().padStart(2, "0")}-${day.toString().padStart(2, "0")}`
      const dayElement = document.createElement("div")

      dayElement.className = "calendar-day"
      dayElement.textContent = day
      dayElement.dataset.date = dateStr

      if (dateStr === today.toISOString().split("T")[0]) {
        dayElement.classList.add("current-day")
      }

      if (dateStr === selectedDate) {
        dayElement.classList.add("selected")
      }

      dayElement.addEventListener("click", function () {
        document.querySelector(".selected")?.classList.remove("selected")
        this.classList.add("selected")
        selectedDate = dateStr
        selectedDateLabel.textContent = formatearFecha(selectedDate)
        mostrarEventos(selectedDate)
      })

      calendar.appendChild(dayElement)
    }

    // Cargar eventos para el mes actual
    cargarEventosMes()
  }

  // Función para cargar eventos del mes actual y marcar días con eventos
  async function cargarEventosMes() {
    const primerDia = `${currentYear}-${(currentMonth + 1).toString().padStart(2, "0")}-01`
    const ultimoDia = `${currentYear}-${(currentMonth + 1).toString().padStart(2, "0")}-${new Date(currentYear, currentMonth + 1, 0).getDate()}`

    try {
      const response = await fetch(`api/eventos.php?inicio=${primerDia}&fin=${ultimoDia}`)
      if (!response.ok) {
        throw new Error("Error al cargar eventos del mes")
      }

      const eventos = await response.json()

      // Crear un conjunto de fechas con eventos
      const fechasConEventos = new Set()
      eventos.forEach((evento) => {
        fechasConEventos.add(evento.fecha)
      })

      // Marcar días con eventos
      document.querySelectorAll(".calendar-day").forEach((day) => {
        if (fechasConEventos.has(day.dataset.date)) {
          day.classList.add("has-events")
        }
      })
    } catch (error) {
      console.error("Error:", error)
    }
  }

  // Añadir animaciones y mejoras visuales al cargar eventos
  async function mostrarEventos(fecha) {
    eventList.innerHTML = "" // Limpiar lista
    selectedDateLabel.textContent = formatearFecha(fecha) // Mostrar la fecha seleccionada

    try {
      const eventos = await cargarEventos(fecha)

      if (eventos.length === 0) {
        eventList.innerHTML = "<li class='no-events'>No hay eventos para este día</li>"
      } else {
        eventos.forEach((evento, index) => {
          const li = document.createElement("li")
          li.innerHTML = `
          <strong>${evento.titulo}</strong> 
          <span class="event-time">🕒 ${evento.hora}</span>
          <p class="event-description">📄 ${evento.descripcion || ""}</p>
        `

          // Añadir animación de entrada
          li.style.opacity = "0"
          li.style.transform = "translateY(20px)"
          li.style.transition = "opacity 0.3s ease, transform 0.3s ease"

          // Botón para eliminar
          const delBtn = document.createElement("button")
          delBtn.textContent = "❌"
          delBtn.className = "delete-btn"
          delBtn.onclick = async () => {
            if (confirm("¿Estás seguro de que deseas eliminar este evento?")) {
              const resultado = await eliminarEvento(evento.id)
              if (resultado && resultado.success) {
                li.style.opacity = "0"
                li.style.transform = "translateX(50px)"
                setTimeout(() => {
                  mostrarEventos(fecha)
                  cargarEventosMes()
                }, 300)
              }
            }
          }

          // Botón para marcar como completado
          const doneBtn = document.createElement("button")
          doneBtn.textContent = evento.completado ? "✓" : "✅"
          doneBtn.className = "complete-btn"
          if (evento.completado) {
            li.classList.add("completed")
          }

          doneBtn.onclick = async () => {
            const nuevoEstado = !evento.completado
            const resultado = await actualizarEvento(evento.id, { completado: nuevoEstado })
            if (resultado) {
              if (nuevoEstado) {
                li.classList.add("completed")
                doneBtn.textContent = "✓"
              } else {
                li.classList.remove("completed")
                doneBtn.textContent = "✅"
              }
            }
          }

          li.appendChild(doneBtn)
          li.appendChild(delBtn)
          eventList.appendChild(li)

          // Aplicar animación con un pequeño retraso para cada elemento
          setTimeout(() => {
            li.style.opacity = "1"
            li.style.transform = "translateY(0)"
          }, index * 100)
        })
      }
    } catch (error) {
      console.error("Error:", error)
      eventList.innerHTML = "<li class='error'>Error al cargar eventos</li>"
    }
  }

  // Función para formatear fecha en formato legible
  function formatearFecha(fechaStr) {
    const fecha = new Date(fechaStr)
    return fecha.toLocaleDateString("es-ES", {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
    })
  }

  // Manejar envío del formulario
  form.addEventListener("submit", async (event) => {
    event.preventDefault()

    const titulo = document.getElementById("titulo").value.trim()
    const hora = document.getElementById("hora").value
    const descripcion = document.getElementById("descripcion").value.trim()
    const fechaEvento = dateInput.value

    if (!titulo || !hora || !fechaEvento) {
      alert("Por favor, completa todos los campos obligatorios.")
      return
    }

    const nuevoEvento = {
      titulo,
      fecha: fechaEvento,
      hora,
      descripcion,
    }

    const eventoGuardado = await guardarEvento(nuevoEvento)

    if (eventoGuardado) {
      form.reset()

      // Si la fecha del evento es la seleccionada actualmente, actualizar la lista
      if (fechaEvento === selectedDate) {
        mostrarEventos(selectedDate)
      }

      // Actualizar marcadores de eventos en el calendario
      cargarEventosMes()
    } else {
      alert("Error al guardar el evento. Por favor, inténtalo de nuevo.")
    }
  })

  // Navegación del calendario
  prevMonthBtn.addEventListener("click", () => {
    currentMonth--
    if (currentMonth < 0) {
      currentMonth = 11
      currentYear--
    }
    actualizarCalendario()
  })

  nextMonthBtn.addEventListener("click", () => {
    currentMonth++
    if (currentMonth > 11) {
      currentMonth = 0
      currentYear++
    }
    actualizarCalendario()
  })

  // Inicializar
  actualizarCalendario()
  mostrarEventos(selectedDate)
})
