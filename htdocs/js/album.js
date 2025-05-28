document.addEventListener("DOMContentLoaded", () => {
  const lightbox = document.getElementById("lightbox")
  const lightboxImg = document.getElementById("lightbox-img")
  const lightboxInfo = document.getElementById("lightbox-info")
  const closeBtn = document.getElementById("close")
  const uploadForm = document.getElementById("upload-form")

  // Función para mostrar imagen en el lightbox
  function showImage(img, nombre, descripcion) {
    lightboxImg.src = img.src
    lightboxImg.alt = img.alt

    // Mostrar información de la foto
    lightboxInfo.innerHTML = `
            <h3>${nombre}</h3>
            ${descripcion ? `<p>${descripcion}</p>` : ""}
        `

    lightbox.classList.remove("hidden")
    lightbox.classList.add("visible")
  }

  // Cerrar lightbox al hacer clic en el botón de cerrar
  closeBtn.addEventListener("click", () => {
    lightbox.classList.remove("visible")
    lightbox.classList.add("hidden")
  })

  // Cerrar lightbox al hacer clic fuera de la imagen
  lightbox.addEventListener("click", (event) => {
    if (event.target === lightbox) {
      lightbox.classList.remove("visible")
      lightbox.classList.add("hidden")
    }
  })

  // Configurar eventos para las fotos
  document.querySelectorAll(".photo img").forEach((img) => {
    const photoDiv = img.parentElement
    const nombre = photoDiv.querySelector("h3")?.textContent || "Sin título"
    const descripcion = photoDiv.querySelector("p")?.textContent || ""

    img.addEventListener("click", () => {
      showImage(img, nombre, descripcion)
    })
  })

  // Manejar eliminación de fotos
  document.querySelectorAll(".delete-photo").forEach((button) => {
    button.addEventListener("click", async function (e) {
      e.stopPropagation()
      const photoId = this.dataset.id

      if (confirm("¿Estás seguro de que deseas eliminar esta foto?")) {
        try {
          const response = await fetch(`../api/fotos.php?id=${photoId}`, {
            method: "DELETE",
          })

          if (!response.ok) {
            throw new Error("Error al eliminar la foto")
          }

          const result = await response.json()

          if (result.success) {
            // Eliminar la foto del DOM
            const photoElement = document.querySelector(`.photo[data-id="${photoId}"]`)
            if (photoElement) {
              photoElement.remove()
            }

            // Si no hay más fotos, mostrar mensaje
            if (document.querySelectorAll(".photo").length === 0) {
              const gallery = document.querySelector(".gallery")
              gallery.innerHTML = '<p class="no-photos">No hay fotos en tu álbum. ¡Sube algunas!</p>'
            }
          } else {
            alert("Error al eliminar la foto: " + (result.error || "Error desconocido"))
          }
        } catch (error) {
          console.error("Error:", error)
          alert("Error al eliminar la foto")
        }
      }
    })
  })

  // Manejar subida de fotos
  if (uploadForm) {
    uploadForm.addEventListener("submit", async function (e) {
      e.preventDefault()

      const formData = new FormData(this)

      try {
        const response = await fetch("../api/fotos.php", {
          method: "POST",
          body: formData,
        })

        if (!response.ok) {
          throw new Error("Error al subir la foto")
        }

        const result = await response.json()

        if (result.id) {
          // Recargar la página para mostrar la nueva foto
          window.location.reload()
        } else {
          alert("Error al subir la foto: " + (result.error || "Error desconocido"))
        }
      } catch (error) {
        console.error("Error:", error)
        alert("Error al subir la foto")
      }
    })
  }
})
