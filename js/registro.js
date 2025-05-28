
document.addEventListener('DOMContentLoaded', function () {
    const cuidadorBtn = document.getElementById('cuidadorBtn');
    const adultoBtn = document.getElementById('adultoBtn');
    const formCuidador = document.getElementById('formCuidador');
    const formAdulto = document.getElementById('formAdulto');

    // Función para activar un formulario y botón
    function activarFormulario(rol) {
        if (rol === 'cuidador') {
            formCuidador.style.display = 'block';
            formAdulto.style.display = 'none';
            cuidadorBtn.classList.add('active');
            adultoBtn.classList.remove('active');
        } else if (rol === 'adulto') {
            formCuidador.style.display = 'none';
            formAdulto.style.display = 'block';
            adultoBtn.classList.add('active');
            cuidadorBtn.classList.remove('active');
        }
    }

    // Eventos de los botones
    cuidadorBtn.addEventListener('click', function (e) {
        e.preventDefault(); // Evita que recargue la página si está dentro de un <form>
        activarFormulario('cuidador');
    });

    adultoBtn.addEventListener('click', function (e) {
        e.preventDefault();
        activarFormulario('adulto');
    });

    // Mostrar inicialmente el de cuidador si quieres
    activarFormulario('cuidador');
});