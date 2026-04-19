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
    const horasInput = document.getElementById('horas_jugadas');
    const minutosInput = document.getElementById('minutos_jugados');
    const fechaInicioInput = document.getElementById('fecha_inicio');
    const fechaFinInput = document.getElementById('fecha_fin');
    const campoFechaFin = document.querySelector('.campo-fecha-fin');

    function actualizarFechaFin() {
        if (estadoInput.value === 'completado') {
            campoFechaFin.classList.remove('oculto');
        } else {
            campoFechaFin.classList.add('oculto');
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
        actualizarFechaFin();
        validarSelect(estadoInput);
        validarOrdenFechas();
    });

    plataformaInput.addEventListener('change', function() {
        validarSelect(plataformaInput);
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

    actualizarFechaFin();

    formBiblioteca.addEventListener('submit', function(e) {
        let valido = true;

        if (!validarSelect(estadoInput)) {
            valido = false;
        }

        if (!validarSelect(plataformaInput)) {
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
