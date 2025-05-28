document.addEventListener("DOMContentLoaded", () => {
  const postsContainer = document.querySelector(".posts")
  const openModal = document.getElementById("openModal")
  const postModal = document.getElementById("postModal")
  const closeModal = document.querySelector(".close")
  const newPostForm = document.getElementById("new-post-form")
  const categoryFilters = document.querySelectorAll(".category-filter")
  const searchInput = document.getElementById("search-input")

  // Abrir y cerrar modal
  openModal.addEventListener("click", () => {
    postModal.style.display = "block"
  })

  closeModal.addEventListener("click", () => {
    postModal.style.display = "none"
  })

  // Cerrar modal al hacer clic fuera del contenido
  window.addEventListener("click", (event) => {
    if (event.target === postModal) {
      postModal.style.display = "none"
    }
  })

  // Enviar nueva publicación
  newPostForm.addEventListener("submit", async (e) => {
    e.preventDefault()

    const formData = new FormData(newPostForm)

    try {
      const response = await fetch("api/foro.php", {
        method: "POST",
        body: formData,
      })

      if (!response.ok) {
        throw new Error("Error al crear la publicación")
      }

      const result = await response.json()

      if (result.success) {
        // Recargar la página para mostrar la nueva publicación
        window.location.reload()
      } else {
        alert(result.error || "Error al crear la publicación")
      }
    } catch (error) {
      console.error("Error:", error)
      alert("Error al crear la publicación. Por favor, inténtalo de nuevo.")
    }
  })

  // Filtrar publicaciones por categoría
  categoryFilters.forEach((filter) => {
    filter.addEventListener("click", function () {
      // Quitar clase activa de todos los filtros
      categoryFilters.forEach((f) => f.classList.remove("active"))
      // Añadir clase activa al filtro seleccionado
      this.classList.add("active")

      const selectedCategory = this.getAttribute("data-category")
      const posts = document.querySelectorAll(".post-card")

      posts.forEach((post) => {
        if (selectedCategory === "Todos" || post.getAttribute("data-category") === selectedCategory) {
          post.style.display = "flex"
        } else {
          post.style.display = "none"
        }
      })
    })
  })

  // Buscar publicaciones
  searchInput.addEventListener("input", function () {
    const searchTerm = this.value.toLowerCase()
    const posts = document.querySelectorAll(".post-card")

    posts.forEach((post) => {
      const title = post.querySelector("h3").textContent.toLowerCase()
      const content = post.querySelector("p").textContent.toLowerCase()
      const author = post.querySelector("h4").textContent.toLowerCase()

      if (title.includes(searchTerm) || content.includes(searchTerm) || author.includes(searchTerm)) {
        post.style.display = "flex"
      } else {
        post.style.display = "none"
      }
    })
  })

  // Manejar likes
  document.querySelectorAll(".like-button").forEach((button) => {
    button.addEventListener("click", async function () {
      const postId = this.getAttribute("data-id")
      const likeCount = Number.parseInt(this.getAttribute("data-count"))

      try {
        const response = await fetch("api/foro.php?action=like&id=" + postId, {
          method: "POST",
        })

        if (!response.ok) {
          throw new Error("Error al dar like")
        }

        const result = await response.json()

        if (result.success) {
          // Actualizar contador de likes
          this.innerHTML = `<i class="fas fa-thumbs-up"></i> ${result.likes}`
          this.setAttribute("data-count", result.likes)
        } else {
          alert(result.error || "Error al dar like")
        }
      } catch (error) {
        console.error("Error:", error)
      }
    })
  })

  // Mostrar/ocultar sección de comentarios
  document.querySelectorAll(".comment-button").forEach((button) => {
    button.addEventListener("click", async function () {
      const postId = this.getAttribute("data-id")
      const postCard = this.closest(".post-card")
      const commentsSection = postCard.querySelector(".comments-section")
      const commentsContainer = postCard.querySelector(".comments")

      // Alternar visibilidad de la sección de comentarios
      if (commentsSection.style.display === "none" || commentsSection.style.display === "") {
        commentsSection.style.display = "block"

        // Cargar comentarios
        try {
          const response = await fetch("api/foro.php?action=getComments&id=" + postId)

          if (!response.ok) {
            throw new Error("Error al cargar comentarios")
          }

          const result = await response.json()

          if (result.success) {
            commentsContainer.innerHTML = ""

            if (result.comments.length === 0) {
              commentsContainer.innerHTML = "<p>No hay comentarios todavía. ¡Sé el primero en comentar!</p>"
            } else {
              result.comments.forEach((comment) => {
                const commentElement = document.createElement("div")
                commentElement.classList.add("comment")
                commentElement.dataset.id = comment.id

                // Verificar si el comentario es del usuario actual
                const isCurrentUserComment = comment.usuario_id == comment.current_user_id

                const deleteButton = isCurrentUserComment
                  ? `<button class="delete-comment-btn" data-id="${comment.id}"><i class="fas fa-trash-alt"></i></button>`
                  : ""

                commentElement.innerHTML = `
                  <div class="comment-author">${comment.nombre} ${comment.apellidos} ${deleteButton}</div>
                  <div>${comment.contenido}</div>
                  <small>${comment.fecha}</small>
                `
                commentsContainer.appendChild(commentElement)
              })

              // Añadir event listeners para los botones de eliminar comentarios
              document.querySelectorAll(".delete-comment-btn").forEach((btn) => {
                btn.addEventListener("click", async function () {
                  const commentId = this.dataset.id
                  if (confirm("¿Estás seguro de que deseas eliminar este comentario?")) {
                    try {
                      const response = await fetch(`api/foro.php?action=deleteComment&id=${commentId}`, {
                        method: "DELETE",
                      })

                      if (!response.ok) {
                        throw new Error("Error al eliminar comentario")
                      }

                      const result = await response.json()

                      if (result.success) {
                        // Eliminar el comentario del DOM
                        const commentElement = this.closest(".comment")
                        commentElement.style.opacity = "0"
                        commentElement.style.height = "0"
                        commentElement.style.margin = "0"
                        commentElement.style.padding = "0"
                        commentElement.style.transition = "all 0.3s ease"

                        setTimeout(() => {
                          commentElement.remove()

                          // Actualizar contador de comentarios
                          const commentButton = postCard.querySelector(".comment-button")
                          const commentCount = Number.parseInt(commentButton.textContent.match(/\d+/)[0]) - 1
                          commentButton.innerHTML = `<i class="fas fa-comment"></i> ${commentCount}`

                          // Si no hay más comentarios, mostrar mensaje
                          if (commentsContainer.querySelectorAll(".comment").length === 0) {
                            commentsContainer.innerHTML =
                              "<p>No hay comentarios todavía. ¡Sé el primero en comentar!</p>"
                          }
                        }, 300)
                      } else {
                        alert(result.error || "Error al eliminar comentario")
                      }
                    } catch (error) {
                      console.error("Error:", error)
                      alert("Error al eliminar comentario")
                    }
                  }
                })
              })
            }
          } else {
            commentsContainer.innerHTML = "<p>Error al cargar comentarios</p>"
          }
        } catch (error) {
          console.error("Error:", error)
          commentsContainer.innerHTML = "<p>Error al cargar comentarios</p>"
        }
      } else {
        commentsSection.style.display = "none"
      }
    })
  })

  // Añadir comentario
  document.querySelectorAll(".add-comment").forEach((button) => {
    button.addEventListener("click", async function () {
      const postId = this.getAttribute("data-id")
      const commentsSection = this.closest(".comments-section")
      const commentInput = commentsSection.querySelector(".comment-input")
      const commentsContainer = commentsSection.querySelector(".comments")
      const commentText = commentInput.value.trim()

      if (commentText === "") {
        alert("Por favor, escribe un comentario")
        return
      }

      try {
        const response = await fetch("api/foro.php?action=addComment&id=" + postId, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ contenido: commentText }),
        })

        if (!response.ok) {
          throw new Error("Error al añadir comentario")
        }

        const result = await response.json()

        if (result.success) {
          // Limpiar input
          commentInput.value = ""

          // Actualizar contador de comentarios
          const commentButton = this.closest(".post-card").querySelector(".comment-button")
          const commentCount = Number.parseInt(commentButton.textContent.match(/\d+/)[0]) + 1
          commentButton.innerHTML = `<i class="fas fa-comment"></i> ${commentCount}`

          // Añadir comentario a la lista
          const commentElement = document.createElement("div")
          commentElement.classList.add("comment")
          commentElement.dataset.id = result.comment.id

          // Añadir botón de eliminar si es el comentario del usuario actual
          const deleteButton = `<button class="delete-comment-btn" data-id="${result.comment.id}"><i class="fas fa-trash-alt"></i></button>`

          commentElement.innerHTML = `
            <div class="comment-author">${result.comment.nombre} ${result.comment.apellidos} ${deleteButton}</div>
            <div>${result.comment.contenido}</div>
            <small>${result.comment.fecha}</small>
          `

          // Si es el primer comentario, limpiar el mensaje de "no hay comentarios"
          if (commentsContainer.querySelector("p")?.textContent.includes("No hay comentarios")) {
            commentsContainer.innerHTML = ""
          }

          commentsContainer.appendChild(commentElement)

          // Añadir event listener para el botón de eliminar
          const deleteBtn = commentElement.querySelector(".delete-comment-btn")
          if (deleteBtn) {
            deleteBtn.addEventListener("click", async function () {
              const commentId = this.dataset.id
              if (confirm("¿Estás seguro de que deseas eliminar este comentario?")) {
                try {
                  const response = await fetch(`api/foro.php?action=deleteComment&id=${commentId}`, {
                    method: "DELETE",
                  })

                  if (!response.ok) {
                    throw new Error("Error al eliminar comentario")
                  }

                  const result = await response.json()

                  if (result.success) {
                    // Eliminar el comentario del DOM con animación
                    commentElement.style.opacity = "0"
                    commentElement.style.height = "0"
                    commentElement.style.margin = "0"
                    commentElement.style.padding = "0"
                    commentElement.style.transition = "all 0.3s ease"

                    setTimeout(() => {
                      commentElement.remove()

                      // Actualizar contador de comentarios
                      const commentButton = commentElement.closest(".post-card").querySelector(".comment-button")
                      const commentCount = Number.parseInt(commentButton.textContent.match(/\d+/)[0]) - 1
                      commentButton.innerHTML = `<i class="fas fa-comment"></i> ${commentCount}`

                      // Si no hay más comentarios, mostrar mensaje
                      if (commentsContainer.querySelectorAll(".comment").length === 0) {
                        commentsContainer.innerHTML = "<p>No hay comentarios todavía. ¡Sé el primero en comentar!</p>"
                      }
                    }, 300)
                  } else {
                    alert(result.error || "Error al eliminar comentario")
                  }
                } catch (error) {
                  console.error("Error:", error)
                  alert("Error al eliminar comentario")
                }
              }
            })
          }
        } else {
          alert(result.error || "Error al añadir comentario")
        }
      } catch (error) {
        console.error("Error:", error)
        alert("Error al añadir comentario. Por favor, inténtalo de nuevo.")
      }
    })
  })

  // Eliminar publicación
  document.querySelectorAll(".delete-post-btn").forEach((btn) => {
    btn.addEventListener("click", async function (e) {
      e.stopPropagation()
      const postId = this.dataset.id

      if (confirm("¿Estás seguro de que deseas eliminar esta publicación?")) {
        try {
          const response = await fetch(`api/foro.php?action=deletePost&id=${postId}`, {
            method: "DELETE",
          })

          if (!response.ok) {
            throw new Error("Error al eliminar publicación")
          }

          const result = await response.json()

          if (result.success) {
            // Eliminar la publicación del DOM con animación
            const postCard = this.closest(".post-card")
            postCard.style.opacity = "0"
            postCard.style.transform = "translateY(-20px)"
            postCard.style.transition = "all 0.3s ease"

            setTimeout(() => {
              postCard.remove()

              // Si no hay más publicaciones, mostrar mensaje
              if (document.querySelectorAll(".post-card").length === 0) {
                postsContainer.innerHTML =
                  '<p class="no-posts">No hay publicaciones todavía. ¡Sé el primero en publicar!</p>'
              }
            }, 300)
          } else {
            alert(result.error || "Error al eliminar publicación")
          }
        } catch (error) {
          console.error("Error:", error)
          alert("Error al eliminar publicación")
        }
      }
    })
  })

  // Manejar clic en botón de eliminar
  document.querySelectorAll(".eliminar-btn").forEach((btn) => {
    btn.addEventListener("click", async function () {
      if (confirm("¿Estás seguro de que deseas eliminar este recordatorio?")) {
        const recordatorioId = this.dataset.id
        const recordatoriosList = document.querySelector(".recordatorios-list")

        try {
          const response = await fetch(`api/eventos.php?id=${recordatorioId}`, {
            method: "DELETE",
          })

          if (!response.ok) {
            throw new Error("Error al eliminar el recordatorio")
          }

          // Eliminar elemento del DOM con animación
          const recordatorioItem = this.closest(".recordatorio-item")
          recordatorioItem.style.opacity = "0"
          recordatorioItem.style.transform = "translateX(50px)"

          setTimeout(() => {
            recordatorioItem.remove()

            // Si no hay más recordatorios, mostrar mensaje
            if (document.querySelectorAll(".recordatorio-item").length === 0) {
              recordatoriosList.innerHTML = `
                <div class="no-recordatorios">
                  <p>No tienes recordatorios. ¡Añade uno nuevo!</p>
                </div>
              `
            }
          }, 300)
        } catch (error) {
          console.error("Error:", error)
          alert("Error al eliminar el recordatorio. Por favor, inténtalo de nuevo.")
        }
      }
    })
  })
})
