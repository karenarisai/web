document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("modal-recordatorio");
  const cerrarModal = document.querySelector(".cerrar-modal");
  const form = document.getElementById("form-recordatorio");
  const recordatoriosList = document.querySelector(".recordatorios-lista");
  const filtros = document.querySelectorAll(".filtro-btn");

  const nuevoMedicamentoBtn = document.getElementById("nuevo-medicamento");
  const nuevaCitaBtn = document.getElementById("nueva-cita");
  const nuevoBtn = document.getElementById("nuevo-recordatorio");
  const tipoInput = document.getElementById("recordatorio-tipo");
  const medicamentoFields = document.querySelectorAll(".medicamento-fields");
  const citaFields = document.querySelectorAll(".cita-fields");

  // Función para mostrar modal con tipo y campos
  function abrirModal(tipo, tituloModal) {
    document.getElementById("modal-titulo").textContent = tituloModal;
    document.getElementById("recordatorio-id").value = "";
    tipoInput.value = tipo || "otro";
    form.reset();

    // Mostrar campos según tipo
    medicamentoFields.forEach(field => field.style.display = (tipo === "medicamento") ? "block" : "none");
    citaFields.forEach(field => field.style.display = (tipo === "cita") ? "block" : "none");

    modal.style.display = "block";
  }

  // Eventos para abrir modal
  nuevoMedicamentoBtn.addEventListener("click", () => abrirModal("medicamento", "Nuevo Medicamento"));
  nuevaCitaBtn.addEventListener("click", () => abrirModal("cita", "Nueva Cita Médica"));
  nuevoBtn.addEventListener("click", () => abrirModal("otro", "Nuevo Recordatorio"));

  // Cerrar modal
  cerrarModal.addEventListener("click", () => {
    modal.style.display = "none";
  });

  // Cerrar modal al hacer clic fuera del contenido
  window.addEventListener("click", (event) => {
    if (event.target === modal) {
      modal.style.display = "none";
    }
  });

  // Filtrar recordatorios
  filtros.forEach(filtro => {
    filtro.addEventListener("click", () => {
      filtros.forEach(f => f.classList.remove("active"));
      filtro.classList.add("active");

      const tipoFiltro = filtro.dataset.filtro;
      const recordatorios = document.querySelectorAll(".recordatorio-item");

      recordatorios.forEach(recordatorio => {
        const tipo = recordatorio.dataset.tipo;
        const esCompletado = recordatorio.classList.contains("completado");

        if (tipoFiltro === "todos" ||
          (tipoFiltro === "medicamentos" && tipo === "medicamento" && !esCompletado) ||
          (tipoFiltro === "citas" && tipo === "cita" && !esCompletado) ||
          (tipoFiltro === "completados" && esCompletado)) {
          recordatorio.style.display = "flex";
        } else {
          recordatorio.style.display = "none";
        }
      });
    });
  });

  // Enviar formulario
  form.addEventListener("submit", async event => {
    event.preventDefault();

    const recordatorioId = document.getElementById("recordatorio-id").value;
    const titulo = document.getElementById("titulo").value.trim();
    const fecha = document.getElementById("fecha").value;
    const hora = document.getElementById("hora").value;
    const descripcion = document.getElementById("descripcion").value.trim();
    const completado = document.getElementById("completado").checked;
    const tipo = tipoInput.value;

    if (!titulo || !fecha) {
      alert("Por favor, completa los campos obligatorios.");
      return;
    }

    const detalles = {};
    if (tipo === "medicamento") {
      detalles.dosis = document.getElementById("dosis").value.trim();
      detalles.frecuencia = document.getElementById("frecuencia").value;
    } else if (tipo === "cita") {
      detalles.doctor = document.getElementById("doctor").value.trim();
      detalles.lugar = document.getElementById("lugar").value.trim();
    }

    const recordatorio = {
      titulo,
      fecha,
      hora,
      descripcion,
      completado: completado ? 1 : 0,
      tipo,
      detalles: JSON.stringify(detalles)
    };

    try {
      let response;

      if (recordatorioId) {
        response = await fetch(`api/eventos.php?id=${recordatorioId}`, {
          method: "PUT",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(recordatorio),
        });
      } else {
        response = await fetch("api/eventos.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(recordatorio),
        });
      }

      if (!response.ok) throw new Error("Error al guardar el recordatorio");

      window.location.reload();
    } catch (error) {
      console.error(error);
      alert("Error al guardar el recordatorio. Por favor, inténtalo de nuevo.");
    }
  });

  // Marcar como completado
  document.querySelectorAll(".recordatorio-check input").forEach(checkbox => {
    checkbox.addEventListener("change", async function () {
      const recordatorioItem = this.closest(".recordatorio-item");
      const recordatorioId = recordatorioItem.dataset.id;
      const completado = this.checked;

      try {
        recordatorioItem.style.transition = "all 0.3s ease";
        recordatorioItem.style.backgroundColor = completado ? "#f0f0f0" : "#f9f9f9";
        recordatorioItem.style.borderLeftColor = completado ? "#4CAF50" : "#837eb1";

        const response = await fetch(`api/eventos.php?id=${recordatorioId}`, {
          method: "PUT",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ completado: completado ? 1 : 0 }),
        });

        if (!response.ok) throw new Error("Error al actualizar el recordatorio");

        if (completado) {
          recordatorioItem.classList.add("completado");
        } else {
          recordatorioItem.classList.remove("completado");
        }
      } catch (error) {
        console.error(error);
        alert("Error al actualizar el recordatorio. Por favor, inténtalo de nuevo.");
        this.checked = !completado;
        recordatorioItem.style.backgroundColor = "";
        recordatorioItem.style.borderLeftColor = "";
      }
    });
  });

  // Editar recordatorio
  document.querySelectorAll(".editar-btn").forEach(btn => {
    btn.addEventListener("click", async function () {
      const recordatorioId = this.dataset.id;
      try {
        const response = await fetch(`api/eventos.php?id=${recordatorioId}`);
        if (!response.ok) throw new Error("Error al obtener el recordatorio");

        const recordatorio = await response.json();

        document.getElementById("modal-titulo").textContent = "Editar Recordatorio";
        document.getElementById("recordatorio-id").value = recordatorio.id;
        tipoInput.value = recordatorio.tipo || "otro";
        document.getElementById("titulo").value = recordatorio.titulo;
        document.getElementById("fecha").value = recordatorio.fecha;
        document.getElementById("hora").value = recordatorio.hora;
        document.getElementById("descripcion").value = recordatorio.descripcion || "";
        document.getElementById("completado").checked = recordatorio.completado === "1";

        medicamentoFields.forEach(field => field.style.display = "none");
        citaFields.forEach(field => field.style.display = "none");

        if (recordatorio.tipo === "medicamento") {
          medicamentoFields.forEach(field => (field.style.display = "block"));
          if (recordatorio.detalles) {
            try {
              const detalles = JSON.parse(recordatorio.detalles);
              document.getElementById("dosis").value = detalles.dosis || "";
              document.getElementById("frecuencia").value = detalles.frecuencia || "diaria";
            } catch (e) {
              console.error("Error al parsear detalles:", e);
            }
          }
        } else if (recordatorio.tipo === "cita") {
          citaFields.forEach(field => (field.style.display = "block"));
          if (recordatorio.detalles) {
            try {
              const detalles = JSON.parse(recordatorio.detalles);
              document.getElementById("doctor").value = detalles.doctor || "";
              document.getElementById("lugar").value = detalles.lugar || "";
            } catch (e) {
              console.error("Error al parsear detalles:", e);
            }
          }
        }

        modal.style.display = "block";
      } catch (error) {
        console.error(error);
        alert("Error al cargar el recordatorio. Por favor, inténtalo de nuevo.");
      }
    });
  });

  // Eliminar recordatorio
  document.querySelectorAll(".eliminar-btn").forEach(btn => {
    btn.addEventListener("click", async function () {
      const recordatorioId = this.dataset.id;
      if (confirm("¿Seguro que deseas eliminar este recordatorio?")) {
        try {
          const response = await fetch(`api/eventos.php?id=${recordatorioId}`, {
            method: "DELETE",
          });
          if (!response.ok) throw new Error("Error al eliminar el recordatorio");

          window.location.reload();
        } catch (error) {
          console.error(error);
          alert("Error al eliminar el recordatorio. Por favor, inténtalo de nuevo.");
        }
      }
    });
  });
});

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && modal.style.display === "block") {
    modal.style.display = "none";
  }
});
// Cerrar modal al hacer clic en el botón de cerrar