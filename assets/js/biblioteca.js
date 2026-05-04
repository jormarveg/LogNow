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
    const selectorPuntuacion = document.getElementById('selector-puntuacion');
    const textoPuntuacion = document.getElementById('texto-puntuacion');
    const limpiarPuntuacion = document.getElementById('limpiar-puntuacion');
    const botonesPuntuacion = selectorPuntuacion ? selectorPuntuacion.querySelectorAll('.estrella-puntuacion[data-estrella]') : [];

    function actualizarCamposFecha() {
        if (estadoInput.value === 'pendiente') {
            campoFechaInicio.classList.add('oculto');
            campoFechaFin.classList.add('oculto');
            fechaInicioInput.value = '';
            fechaFinInput.value = '';
            limpiarBiblioteca(fechaInicioInput);
            limpiarBiblioteca(fechaFinInput);
        } else if (estadoInput.value === 'completado') {
            campoFechaInicio.classList.remove('oculto');
            campoFechaFin.classList.remove('oculto');
        } else {
            campoFechaInicio.classList.remove('oculto');
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

    function textoEstrellas(valor) {
        if (valor === '' || valor === '0' || valor === 0) {
            return 'Sin puntuar';
        }

        const estrellas = parseInt(valor, 10) / 20;

        return estrellas.toLocaleString('es-ES', {
            minimumFractionDigits: Number.isInteger(estrellas) ? 0 : 1,
            maximumFractionDigits: 1
        }) + ' estrellas';
    }

    function iconoPuntuacion(indice, valor) {
        const puntos = indice * 20;
        const medio = puntos - 10;

        if (valor >= puntos) {
            return 'fa-solid fa-star';
        }

        if (valor === medio) {
            return 'fa-solid fa-star-half-stroke';
        }

        return 'fa-regular fa-star';
    }

    function pintarPuntuacion(valor) {
        const numero = parseInt(valor || '0', 10);

        textoPuntuacion.textContent = textoEstrellas(numero);

        botonesPuntuacion.forEach(function(boton) {
            const estrella = parseInt(boton.dataset.estrella, 10);
            const icono = boton.querySelector('i');

            icono.className = iconoPuntuacion(estrella, numero);
            boton.classList.toggle('activa', numero >= (estrella * 20) - 10);
        });
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
        actualizarCamposFecha();
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

    if (selectorPuntuacion) {
        botonesPuntuacion.forEach(function(boton) {
            boton.addEventListener('mousemove', function(e) {
                const estrella = parseInt(boton.dataset.estrella, 10);
                const rect = boton.getBoundingClientRect();
                const mitadIzquierda = e.clientX - rect.left < rect.width / 2;
                const valor = mitadIzquierda ? (estrella * 20) - 10 : estrella * 20;

                pintarPuntuacion(valor);
            });

            boton.addEventListener('focus', function() {
                pintarPuntuacion(parseInt(boton.dataset.estrella, 10) * 20);
            });

            boton.addEventListener('click', function(e) {
                const estrella = parseInt(boton.dataset.estrella, 10);
                const rect = boton.getBoundingClientRect();
                const mitadIzquierda = e.clientX - rect.left < rect.width / 2;

                puntuacionInput.value = mitadIzquierda ? String((estrella * 20) - 10) : String(estrella * 20);
                pintarPuntuacion(puntuacionInput.value);
                validarPuntuacion();
            });

            boton.addEventListener('keydown', function(e) {
                const estrella = parseInt(boton.dataset.estrella, 10);

                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    puntuacionInput.value = String(estrella * 20);
                    pintarPuntuacion(puntuacionInput.value);
                    validarPuntuacion();
                }
            });
        });

        selectorPuntuacion.addEventListener('mouseleave', function() {
            pintarPuntuacion(puntuacionInput.value);
        });
    }

    if (limpiarPuntuacion) {
        limpiarPuntuacion.addEventListener('click', function() {
            puntuacionInput.value = '';
            pintarPuntuacion('');
            validarPuntuacion();
        });
    }

    actualizarCamposFecha();
    pintarPuntuacion(puntuacionInput.value);

    formBiblioteca.addEventListener('submit', function(e) {
        let valido = true;

        if (!validarSelect(estadoInput)) {
            valido = false;
        }

        if (!validarSelect(plataformaInput)) {
            valido = false;
        }

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
