// Controla el formulario de escribir reseña

const MIN_CARACTERES_COMENTARIO = 20;
const MAX_CARACTERES_COMENTARIO = 2000;

// Muestra error en un campo
function mostrarErrorResena(input, mensaje) {
    const campo = input.closest('.campo');
    const span = campo ? campo.querySelector('.msg-error') : null;

    input.classList.add('invalido');
    input.classList.remove('valido');

    if (span) {
        span.textContent = mensaje;
    }
}

// Elimina error en un campo
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
    // input oculto donde el selector de estrellas guarda valor
    const puntuacionInput = document.getElementById('puntuacion');
    // textarea de la reseña
    const comentarioInput = document.getElementById('comentario');
    // contador de caractreres
    const contadorComentario = document.getElementById('contador-comentario');

    // Valida que la puntuación esté entre las posibles
    function validarPuntuacion() {
        const valor = puntuacionInput.value.trim();

        if (!/^(10|20|30|40|50|60|70|80|90|100)$/.test(valor)) {
            mostrarErrorResena(puntuacionInput, 'Selecciona una puntuación válida');
            return false;
        }

        mostrarOkResena(puntuacionInput);
        return true;
    }

    //Actualiza el contador visible del comentario
    function actualizarContador() {
        const longitud = comentarioInput.value.trim().length;
        contadorComentario.textContent = longitud + '/' + MAX_CARACTERES_COMENTARIO;
    }


    // Comprueba que el comentario tenga longitud en el rango
    function validarComentario() {
        const longitud = comentarioInput.value.trim().length;

        actualizarContador();

        if (longitud < MIN_CARACTERES_COMENTARIO || longitud > MAX_CARACTERES_COMENTARIO) {
            mostrarErrorResena(comentarioInput, 'El comentario debe tener entre ' + MIN_CARACTERES_COMENTARIO + ' y ' + MAX_CARACTERES_COMENTARIO + ' caracteres');
            return false;
        }

        mostrarOkResena(comentarioInput);
        return true;
    }

    // cada vez que se escribe se actualiza el contador
    comentarioInput.addEventListener('input', function() {
        actualizarContador();

        if (comentarioInput.classList.contains('invalido')) {
            validarComentario();
        }
    });

    // cuando se sale del textarea se valida
    comentarioInput.addEventListener('blur', function() {
        validarComentario();
    });

    // al seleccionar puntuación se valida
    iniciarSelectorPuntuacion(function() {
        validarPuntuacion();
    });
    actualizarContador();

    // al enviar el form se valida todo una última vez
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

// modal para confirmar eliminación de reseña
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

    // se peude cerrar con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modalEliminarResena.hidden) {
            cerrarModalEliminarResena();
        }
    });
}
