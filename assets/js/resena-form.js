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

function limpiarResena(input) {
    const campo = input.closest('.campo');
    const span = campo ? campo.querySelector('.msg-error') : null;

    input.classList.remove('invalido', 'valido');

    if (span) {
        span.textContent = '';
    }
}

const formResena = document.getElementById('form-resena');

if (formResena) {
    const puntuacionInput = document.getElementById('puntuacion');
    const comentarioInput = document.getElementById('comentario');
    const selectorPuntuacion = document.getElementById('selector-puntuacion');
    const textoPuntuacion = document.getElementById('texto-puntuacion');
    const limpiarPuntuacion = document.getElementById('limpiar-puntuacion');
    const contadorComentario = document.getElementById('contador-comentario');
    const botonesPuntuacion = selectorPuntuacion ? selectorPuntuacion.querySelectorAll('.estrella-puntuacion[data-estrella]') : [];

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

    comentarioInput.addEventListener('input', function() {
        actualizarContador();

        if (comentarioInput.classList.contains('invalido')) {
            validarComentario();
        }
    });

    comentarioInput.addEventListener('blur', function() {
        validarComentario();
    });

    pintarPuntuacion(puntuacionInput.value);
    actualizarContador();

    formResena.addEventListener('submit', function(e) {
        const puntuacionValida = validarPuntuacion();
        const comentarioValido = validarComentario();

        if (!puntuacionValida || !comentarioValido) {
            e.preventDefault();
        }
    });
}
