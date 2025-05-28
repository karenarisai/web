document.addEventListener("DOMContentLoaded", () => {
  const menuToggle = document.getElementById("menu-toggle")
  const nav = document.querySelector(".nav")

  if (menuToggle && nav) {
    menuToggle.addEventListener("click", () => {
      nav.classList.toggle("active")
    })
  }

  // Cerrar sesión
  const logoutBtn = document.getElementById("logout")
  if (logoutBtn) {
    logoutBtn.addEventListener("click", (e) => {
      e.preventDefault()
      if (confirm("¿Estás seguro de que deseas cerrar sesión?")) {
        window.location.href = "auth/logout.php"
      }
    })
  }
})
