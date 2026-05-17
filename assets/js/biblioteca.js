function mostrarErrorBiblioteca(input, mensaje) {
    const campo = input.closest('.campo');
    const span = campo ? campo.querySelector('.msg-error') : null;

    input.classList.add('invalido');
    input.classList.remove('valido');

    if (span) {
        span.textContent = mensaje;
    }
}

function mostrarOkBiblioteca(input) {
    const campo = input.closest('.campo');
    const span = campo ? campo.querySelector('.msg-error') : null;

    input.classList.remove('invalido');
    input.classList.add('valido');

    if (span) {
        span.textContent = '';
    }
}

function limpiarBiblioteca(input) {
    const campo = input.closest('.campo');
    const span = campo ? campo.querySelector('.msg-error') : null;

    input.classList.remove('invalido', 'valido');

    if (span) {
        span.textContent = '';
    }
}

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
    const estadoInput = document.getElementById('estado');
    const plataformaInput = document.getElementById('plataforma');
    const puntuacionInput = document.getElementById('puntuacion');
    const horasInput = document.getElementById('horas_jugadas');
    const minutosInput = document.getElementById('minutos_jugados');
    const fechaInicioInput = document.getElementById('fecha_inicio');
    const fechaFinInput = document.getElementById('fecha_fin');
    const campoFechaInicio = document.querySelector('.campo-fecha-inicio');
    const campoFechaFin = document.querySelector('.campo-fecha-fin');

    function mostrarCampoFecha(campo, animar) {
        if (!campo) {
            return;
        }

        if (typeof window.jQuery === 'undefined') {
            campo.classList.remove('oculto');
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

    function ocultarCampoFecha(campo, animar) {
        if (!campo) {
            return;
        }

        if (typeof window.jQuery === 'undefined') {
            campo.classList.add('oculto');
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

    function actualizarCamposFecha(animar) {
        if (estadoInput.value === 'pendiente') {
            ocultarCampoFecha(campoFechaInicio, animar);
            ocultarCampoFecha(campoFechaFin, animar);
            fechaInicioInput.value = '';
            fechaFinInput.value = '';
            limpiarBiblioteca(fechaInicioInput);
            limpiarBiblioteca(fechaFinInput);
        } else if (estadoInput.value === 'completado') {
            mostrarCampoFecha(campoFechaInicio, animar);
            mostrarCampoFecha(campoFechaFin, animar);
        } else {
            mostrarCampoFecha(campoFechaInicio, animar);
            ocultarCampoFecha(campoFechaFin, animar);
            fechaFinInput.value = '';
            limpiarBiblioteca(fechaFinInput);
        }
    }

    function validarSelect(input) {
        if (input.value === '' || input.value === '0') {
            mostrarErrorBiblioteca(input, 'Este campo es obligatorio');
            return false;
        }

        mostrarOkBiblioteca(input);
        return true;
    }

    function validarPlataforma() {
        if (plataformaInput.value === '0') {
            limpiarBiblioteca(plataformaInput);
        } else {
            mostrarOkBiblioteca(plataformaInput);
        }

        return true;
    }

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

    function validarFecha(input) {
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

    estadoInput.addEventListener('change', function() {
        actualizarCamposFecha(true);
        validarSelect(estadoInput);
        validarOrdenFechas();
    });

    plataformaInput.addEventListener('change', function() {
        validarPlataforma();
    });

    [horasInput, minutosInput].forEach(function(input) {
        input.addEventListener('blur', function() {
            validarNumero(input, input.id === 'minutos_jugados' ? 59 : null);
        });

        input.addEventListener('input', function() {
            if (input.classList.contains('invalido')) {
                validarNumero(input, input.id === 'minutos_jugados' ? 59 : null);
            }
        });
    });

    [fechaInicioInput, fechaFinInput].forEach(function(input) {
        input.addEventListener('change', function() {
            validarFecha(input);
            validarOrdenFechas();
        });
    });

    actualizarCamposFecha(false);
    iniciarSelectorPuntuacion({
        alCambiar: function() {
            validarPuntuacion();
        }
    });

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
