document.addEventListener("DOMContentLoaded", () => {
  // Cambio de tamaño de fuente
  const fontSizeSlider = document.getElementById("font-size-slider")
  const fontSizeValue = document.getElementById("font-size-value")

  if (fontSizeSlider) {
    fontSizeSlider.addEventListener("input", function () {
      const newSize = this.value
      fontSizeValue.textContent = newSize + "px"
      document.body.style.fontSize = newSize + "px"
    })
  }

  // Modo de alto contraste
  const contrastToggle = document.getElementById("contrast-toggle")
  if (contrastToggle) {
    contrastToggle.addEventListener("change", function () {
      document.body.classList.toggle("high-contrast", this.checked)
    })
  }

  // Lector de pantalla
  const screenReader = document.getElementById("screen-reader")
  if (screenReader) {
    screenReader.addEventListener("change", function () {
      if (this.checked) {
        alert(
          "Lector de pantalla activado. Esta función utilizará la API de voz de su navegador para leer el contenido.",
        )

        // Ejemplo básico de lector de pantalla
        const synth = window.speechSynthesis
        if (synth) {
          const allText = document.body.innerText
          const utterance = new SpeechSynthesisUtterance(
            "Lector de pantalla activado. Puede navegar por la página y el contenido será leído automáticamente.",
          )
          synth.speak(utterance)
        }
      } else {
        alert("Lector de pantalla desactivado.")
      }
    })
  }

  // Modal de edición de perfil
  const editProfileBtn = document.getElementById("edit-profile-btn")
  const editProfileModal = document.getElementById("edit-profile-modal")
  const closeModalBtns = document.querySelectorAll(".close-modal")

  if (editProfileBtn && editProfileModal) {
    editProfileBtn.addEventListener("click", () => {
      editProfileModal.style.display = "block"
    })

    closeModalBtns.forEach((btn) => {
      btn.addEventListener("click", () => {
        btn.closest(".modal").style.display = "none"
      })
    })

    // Cerrar modal al hacer clic fuera
    window.addEventListener("click", (event) => {
      if (event.target.classList.contains("modal")) {
        event.target.style.display = "none"
      }
    })
  }

  // Modal para agregar contacto
  const addContactBtn = document.getElementById("add-contact-btn")
  const addContactModal = document.getElementById("add-contact-modal")
  const closeContactModal = document.getElementById("close-contact-modal")

  if (addContactBtn && addContactModal) {
    addContactBtn.addEventListener("click", () => {
      addContactModal.style.display = "block"
    })

    if (closeContactModal) {
      closeContactModal.addEventListener("click", () => {
        addContactModal.style.display = "none"
      })
    }
  }

  // Confirmar eliminación de contacto
  const deleteContactForms = document.querySelectorAll(".delete-contact-form")
  deleteContactForms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      if (!confirm("¿Está seguro de que desea eliminar este contacto de emergencia?")) {
        e.preventDefault()
      }
    })
  })

  // Cambio de idioma y voz
  const idiomaSelect = document.getElementById("idioma")
  const vozSelect = document.getElementById("voz")

  if (idiomaSelect) {
    idiomaSelect.addEventListener("change", function () {
      // Actualizar opciones de voz según el idioma seleccionado
      const idioma = this.value
      vozSelect.innerHTML = ""

      if (idioma === "es") {
        vozSelect.innerHTML = `
          <option value="Maria">María (Español)</option>
          <option value="Juan">Juan (Español)</option>
        `
      } else if (idioma === "en") {
        vozSelect.innerHTML = `
          <option value="Emma">Emma (Inglés)</option>
          <option value="John">John (Inglés)</option>
        `
      } else if (idioma === "fr") {
        vozSelect.innerHTML = `
          <option value="Sophie">Sophie (Francés)</option>
          <option value="Pierre">Pierre (Francés)</option>
        `
      }
    })
  }

  // Guardar preferencias automáticamente al cambiar
  const autoSaveInputs = document.querySelectorAll("#font-size-slider, #contrast-toggle, #screen-reader")
  autoSaveInputs.forEach((input) => {
    input.addEventListener("change", () => {
      // Opcional: guardar automáticamente al cambiar
      // document.getElementById("form-preferencias").submit()
    })
  })
})
