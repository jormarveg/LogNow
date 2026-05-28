// Controla y valida el formulario de añadir o editar un juego en la biblioteca personal

// Muestra error en un campo
function mostrarErrorBiblioteca(input, mensaje) {
    const campo = input.closest('.campo');
    const span = campo ? campo.querySelector('.msg-error') : null;

    input.classList.add('invalido');
    input.classList.remove('valido');

    if (span) {
        span.textContent = mensaje;
    }
}

// Elimina error en un campo
function mostrarOkBiblioteca(input) {
    const campo = input.closest('.campo');
    const span = campo ? campo.querySelector('.msg-error') : null;

    input.classList.remove('invalido');
    input.classList.add('valido');

    if (span) {
        span.textContent = '';
    }
}

// Elimina las clases valido e invalido y limpia el input
function limpiarBiblioteca(input) {
    const campo = input.closest('.campo');
    const span = campo ? campo.querySelector('.msg-error') : null;

    input.classList.remove('invalido', 'valido');

    if (span) {
        span.textContent = '';
    }
}

// Valida una fecha y devuelve null si campo vacío, false si la fecha no es váida, y objeto Date si la fecha es válida
function parsearFechaBiblioteca(valor) {
    if (valor.trim() === '') {
        return null;
    }

    const partes = valor.split('-');

    if (partes.length !== 3) {
        return false;
    }

    const ano = parseInt(partes[0], 10);
    const mes = parseInt(partes[1], 10) - 1;
    const dia = parseInt(partes[2], 10);
    const fecha = new Date(ano, mes, dia);

    if (fecha.getFullYear() !== ano || fecha.getMonth() !== mes || fecha.getDate() !== dia) {
        return false;
    }

    return fecha;
}

const formBiblioteca = document.getElementById('form-biblioteca');

if (formBiblioteca) {
    // Coge los campos del formulario 
    const estadoInput = document.getElementById('estado');
    const plataformaInput = document.getElementById('plataforma');
    const puntuacionInput = document.getElementById('puntuacion');
    const horasInput = document.getElementById('horas_jugadas');
    const minutosInput = document.getElementById('minutos_jugados');
    const fechaInicioInput = document.getElementById('fecha_inicio');
    const fechaFinInput = document.getElementById('fecha_fin');
    const campoFechaInicio = document.querySelector('.campo-fecha-inicio');
    const campoFechaFin = document.querySelector('.campo-fecha-fin');

    // Muestra el campo de fecha con o sin animación usando jQuery
    function mostrarCampoFecha(campo, animar) {
        if (!campo) {
            return;
        }

        const campoJquery = $(campo);
        const estabaOculto = campoJquery.hasClass('oculto') || !campoJquery.is(':visible');

        campoJquery.stop(true, true).removeClass('oculto');

        if (animar && estabaOculto) {
            campoJquery.hide().slideDown(170, function() {
                campoJquery.css('display', '');
            });
        } else {
            campoJquery.show().css('display', '');
        }
    }

    // Oculta el campo de fecha con o sin animación usando jQuery
    function ocultarCampoFecha(campo, animar) {
        if (!campo) {
            return;
        }

        const campoJquery = $(campo);
        const estabaOculto = campoJquery.hasClass('oculto') || !campoJquery.is(':visible');

        campoJquery.stop(true, true);

        if (animar && !estabaOculto) {
            campoJquery.slideUp(150, function() {
                campoJquery.addClass('oculto').css('display', '');
            });
        } else {
            campoJquery.hide().addClass('oculto').css('display', '');
        }
    }

    // Función que decide qué fechas se ven según el estado del juego para el usuario
    function actualizarCamposFecha(animar) {
        if (estadoInput.value === 'pendiente') {
            ocultarCampoFecha(campoFechaInicio, animar);
            ocultarCampoFecha(campoFechaFin, animar);
            limpiarBiblioteca(fechaInicioInput);
            limpiarBiblioteca(fechaFinInput);
        } else if (estadoInput.value === 'completado') {
            mostrarCampoFecha(campoFechaInicio, animar);
            mostrarCampoFecha(campoFechaFin, animar);
        } else {
            mostrarCampoFecha(campoFechaInicio, animar);
            ocultarCampoFecha(campoFechaFin, animar);
            limpiarBiblioteca(fechaFinInput);
        }
    }

    // Validación para "select" obligatorios
    function validarSelect(input) {
        if (input.value === '' || input.value === '0') {
            mostrarErrorBiblioteca(input, 'Este campo es obligatorio');
            return false;
        }

        mostrarOkBiblioteca(input);
        return true;
    }

    // La platforma es opcional, si no tiene valor solo limpia
    function validarPlataforma() {
        if (plataformaInput.value === '0') {
            limpiarBiblioteca(plataformaInput);
        } else {
            mostrarOkBiblioteca(plataformaInput);
        }

        return true;
    }

    // Valida que la puntuación sea correcta, solo permite puntuaciones válidas de media estrella en media estrella
    function validarPuntuacion() {
        const valor = puntuacionInput.value.trim();

        if (valor === '') {
            limpiarBiblioteca(puntuacionInput);
            return true;
        }

        if (!/^(10|20|30|40|50|60|70|80|90|100)$/.test(valor)) {
            mostrarErrorBiblioteca(puntuacionInput, 'Selecciona una puntuación válida');
            return false;
        }

        mostrarOkBiblioteca(puntuacionInput);
        return true;
    }

    // Valida los campos de horas y minutos
    function validarNumero(input, maximo) {
        let valor = input.value.trim();

        if (valor === '') {
            input.value = '0';
            valor = '0';
        }

        if (!/^\d+$/.test(valor)) {
            mostrarErrorBiblioteca(input, 'Introduce un número válido');
            return false;
        }

        if (maximo !== null && parseInt(valor, 10) > maximo) {
            mostrarErrorBiblioteca(input, 'El valor máximo es ' + maximo);
            return false;
        }

        mostrarOkBiblioteca(input);
        return true;
    }


    // Valida una fecha
    function validarFecha(input) {
        // vacía es válida
        if (input.value.trim() === '') {
            limpiarBiblioteca(input);
            return true;
        }

        if (!parsearFechaBiblioteca(input.value)) {
            mostrarErrorBiblioteca(input, 'La fecha no es válida');
            return false;
        }

        mostrarOkBiblioteca(input);
        return true;
    }

    // Compara fecha inicio y de fin comprobando que la fecha de fin no sea anterior a la de inicio
    function validarOrdenFechas() {
        const inicio = parsearFechaBiblioteca(fechaInicioInput.value);
        const fin = parsearFechaBiblioteca(fechaFinInput.value);

        if (!inicio || !fin) {
            return true;
        }

        if (fin < inicio) {
            mostrarErrorBiblioteca(fechaFinInput, 'La fecha de fin no puede ser anterior');
            return false;
        }

        if (estadoInput.value === 'completado') {
            mostrarOkBiblioteca(fechaFinInput);
        }

        return true;
    }

    // cuando se selecciona otro estado de juego muestra u oculta fechas, valida estado y revisa orden de fechas
    estadoInput.addEventListener('change', function() {
        actualizarCamposFecha(true);
        validarSelect(estadoInput);
        validarOrdenFechas();
    });

    // al cambiar plataforma la valida
    plataformaInput.addEventListener('change', function() {
        validarPlataforma();
    });

    [horasInput, minutosInput].forEach(function(input) {
        // valida solo al salir del campo
        input.addEventListener('blur', function() {
            validarNumero(input, input.id === 'minutos_jugados' ? 59 : null);
        });

        input.addEventListener('input', function() {
            validarNumero(input, input.id === 'minutos_jugados' ? 59 : null);
        });
    });

    // al cambiar fechas valida y revisa orden
    [fechaInicioInput, fechaFinInput].forEach(function(input) {
        input.addEventListener('change', function() {
            validarFecha(input);
            validarOrdenFechas();
        });
    });

    // cuando la página está cargada se ajusta qué fechas se muestran desde el principio
    $(function() {
        actualizarCamposFecha(false);
    });
    // arranca el selector de estrellas y le pasa un callback, 
    // cada vez que cambia la puntuación, la valida
    iniciarSelectorPuntuacion(function() {
        validarPuntuacion();
    });

    // validación final antes de enviar el formulario
    formBiblioteca.addEventListener('submit', function(e) {
        let valido = true;

        if (!validarSelect(estadoInput)) {
            valido = false;
        }

        validarPlataforma();

        if (!validarPuntuacion()) {
            valido = false;
        }

        if (!validarNumero(horasInput, null)) {
            valido = false;
        }

        if (!validarNumero(minutosInput, 59)) {
            valido = false;
        }

        if (!validarFecha(fechaInicioInput)) {
            valido = false;
        }

        if (estadoInput.value === 'completado' && !validarFecha(fechaFinInput)) {
            valido = false;
        }

        if (!validarOrdenFechas()) {
            valido = false;
        }

        if (!valido) {
            e.preventDefault();
        }
    });
}

const modalQuitarBiblioteca = document.getElementById('modalQuitarBiblioteca');
const formQuitarBiblioteca = document.getElementById('form-quitar-biblioteca');

// modal de confirmación para quitar un juego de la biblioteca
if (modalQuitarBiblioteca && formQuitarBiblioteca) {
    const abrirModalQuitar = formQuitarBiblioteca.querySelector('.abrir-modal-quitar');
    const cancelarQuitar = modalQuitarBiblioteca.querySelector('.boton-cancelar-quitar');
    const confirmarQuitar = modalQuitarBiblioteca.querySelector('.boton-confirmar-quitar');
    const fondoQuitar = modalQuitarBiblioteca.querySelector('.modal-quitar-fondo');

    function abrirModal() {
        modalQuitarBiblioteca.hidden = false;
        confirmarQuitar.focus();
    }

    function cerrarModal() {
        modalQuitarBiblioteca.hidden = true;
        abrirModalQuitar.focus();
    }

    abrirModalQuitar.addEventListener('click', abrirModal);
    cancelarQuitar.addEventListener('click', cerrarModal);
    fondoQuitar.addEventListener('click', cerrarModal);

    confirmarQuitar.addEventListener('click', function() {
        formQuitarBiblioteca.submit();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modalQuitarBiblioteca.hidden) {
            cerrarModal();
        }
    });
}
