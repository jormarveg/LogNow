function mostrarErrorResena(input, mensaje) {
    const campo = input.closest('.campo');
    const span = campo ? campo.querySelector('.msg-error') : null;

    input.classList.add('invalido');
    input.classList.remove('valido');

    if (span) {
        span.textContent = mensaje;
    }
}

function mostrarOkResena(input) {
    const campo = input.closest('.campo');
    const span = campo ? campo.querySelector('.msg-error') : null;

    input.classList.remove('invalido');
    input.classList.add('valido');

    if (span) {
        span.textContent = '';
    }
}

const formResena = document.getElementById('form-resena');

if (formResena) {
    const puntuacionInput = document.getElementById('puntuacion');
    const comentarioInput = document.getElementById('comentario');
    const contadorComentario = document.getElementById('contador-comentario');

    function validarPuntuacion() {
        const valor = puntuacionInput.value.trim();

        if (!/^(10|20|30|40|50|60|70|80|90|100)$/.test(valor)) {
            mostrarErrorResena(puntuacionInput, 'Selecciona una puntuación válida');
            return false;
        }

        mostrarOkResena(puntuacionInput);
        return true;
    }

    function actualizarContador() {
        const longitud = comentarioInput.value.trim().length;
        contadorComentario.textContent = longitud + '/2000';
    }

    function validarComentario() {
        const longitud = comentarioInput.value.trim().length;

        actualizarContador();

        if (longitud < 20 || longitud > 2000) {
            mostrarErrorResena(comentarioInput, 'El comentario debe tener entre 20 y 2000 caracteres');
            return false;
        }

        mostrarOkResena(comentarioInput);
        return true;
    }

    comentarioInput.addEventListener('input', function() {
        actualizarContador();

        if (comentarioInput.classList.contains('invalido')) {
            validarComentario();
        }
    });

    comentarioInput.addEventListener('blur', function() {
        validarComentario();
    });

    iniciarSelectorPuntuacion({
        alCambiar: function() {
            validarPuntuacion();
        }
    });
    actualizarContador();

    formResena.addEventListener('submit', function(e) {
        const puntuacionValida = validarPuntuacion();
        const comentarioValido = validarComentario();

        if (!puntuacionValida || !comentarioValido) {
            e.preventDefault();
        }
    });
}

const modalEliminarResena = document.getElementById('modalEliminarResena');
const formsEliminarResena = document.querySelectorAll('.form-eliminar-resena');

if (modalEliminarResena && formsEliminarResena.length > 0) {
    const cancelarEliminarResena = modalEliminarResena.querySelector('.boton-cancelar-quitar');
    const confirmarEliminarResena = modalEliminarResena.querySelector('.boton-confirmar-quitar');
    const fondoEliminarResena = modalEliminarResena.querySelector('.modal-quitar-fondo');
    let formEliminarResena = null;
    let abrirEliminarResena = null;

    function abrirModalEliminarResena(form, boton) {
        formEliminarResena = form;
        abrirEliminarResena = boton;
        modalEliminarResena.hidden = false;
        confirmarEliminarResena.focus();
    }

    function cerrarModalEliminarResena() {
        modalEliminarResena.hidden = true;
        if (abrirEliminarResena) {
            abrirEliminarResena.focus();
        }
    }

    formsEliminarResena.forEach(function(form) {
        const botonAbrir = form.querySelector('.abrir-modal-eliminar-resena');

        if (!botonAbrir) {
            return;
        }

        botonAbrir.addEventListener('click', function() {
            abrirModalEliminarResena(form, botonAbrir);
        });
    });

    cancelarEliminarResena.addEventListener('click', cerrarModalEliminarResena);
    fondoEliminarResena.addEventListener('click', cerrarModalEliminarResena);

    confirmarEliminarResena.addEventListener('click', function() {
        if (formEliminarResena) {
            formEliminarResena.submit();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modalEliminarResena.hidden) {
            cerrarModalEliminarResena();
        }
    });
}
