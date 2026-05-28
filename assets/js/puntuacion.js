const VALOR_ESTRELLA = 20;
const VALOR_MEDIA_ESTRELLA = VALOR_ESTRELLA / 2;

// Función para crear el selector de puntuación con estrellas.
// recibe la función callback que se ejecuta al cambiar la puntuación
function iniciarSelectorPuntuacion(alCambiar = function() {}) {
    // contenedor con las 5 estrellas
    const selector = document.getElementById('selector-puntuacion');
    // input oculto que guarda el valor elegido
    const inputPuntuacion = document.getElementById('puntuacion');
    // elemento que muestra en texto la puntuación elegida
    const texto = document.getElementById('texto-puntuacion');
    // texto clicable para limpiar puntuación
    const limpiar = document.getElementById('limpiar-puntuacion');
    // cada una de las cinco estrellas. Contienen atributo [data-estrella] con el número de la estrella
    const botonesEstrella = selector ? selector.querySelectorAll('.estrella-puntuacion[data-estrella]') : [];

    if (!selector || !inputPuntuacion || !texto || botonesEstrella.length === 0) {
        return null;
    }

    // Devuelve el texto con el número de estrellas
    function textoEstrellas(valor) {
        if (valor === 0) {
            return 'Sin puntuar';
        }
        // entre VALOR_ESTRELLA (20) porque la puntuación llega en escala de 0 a 100
        const estrellas = valor / VALOR_ESTRELLA;

        // si es entero no muestra decimales, si es decimal, muestra 1
        return estrellas.toLocaleString('es-ES', {
            minimumFractionDigits: Number.isInteger(estrellas) ? 0 : 1,
            maximumFractionDigits: 1
        }) + ' estrellas';
    }


    // Decide qué icono de FontAwesome usar: estrella completa, media o vacía
    // Recibe qué estrella pinta (indice) y la puntuación
    function iconoPuntuacion(indice, puntuacion) {
        /*
        Internamente una estrella completa vale 20
        - Estrella 1: 1 * 20 = 20.
        - Estrella 2: 2 * 20 = 40.
        - Estrella 3: 3 * 20 = 60.
        - Estrella 4: 4 * 20 = 80.
        - Estrella 5: 5 * 20 = 100.
        */
        // puntosEstrella es el valor de la estrella pulsada
        const puntosEstrella = indice * VALOR_ESTRELLA;
        const media = puntosEstrella - VALOR_MEDIA_ESTRELLA;

        if (puntuacion >= puntosEstrella) {
            return 'fa-solid fa-star';
        }

        if (puntuacion === media) {
            return 'fa-solid fa-star-half-stroke';
        }

        return 'fa-regular fa-star';
    }

    function actualizarBotonLimpiar(valor) {
        if (limpiar) {
            limpiar.hidden = valor === '' || valor === '0' || valor === 0;
        }
    }

    // Actualiza visualmente el selector de estrellas, recorriendolas
    function pintar(valor, actualizarLimpiar = true) {
        const numero = parseInt(valor || '0', 10);

        // cambia el texto, por ej. "2 estrellas"
        texto.textContent = textoEstrellas(numero);

        if (actualizarLimpiar) {
            actualizarBotonLimpiar(valor);
        }

        // recorre cada estrella y cambia icono y le pone o quita la clase "activa"
        botonesEstrella.forEach(function(boton) {
            const estrella = parseInt(boton.dataset.estrella, 10);
            const icono = boton.querySelector('i');

            icono.className = iconoPuntuacion(estrella, numero);
            boton.classList.toggle('activa', numero >= (estrella * VALOR_ESTRELLA) - VALOR_MEDIA_ESTRELLA);
        });
    }

    // Guarda el valor, lo pinta y ejecuta el callback para
    // avisar a la página que usa el selector
    function guardar(valor) {
        inputPuntuacion.value = valor;
        pintar(valor);
        alCambiar(inputPuntuacion);
    }

    // se añaden eventos a cada estrella
    botonesEstrella.forEach(function(boton) {
        boton.addEventListener('mousemove', function(e) {
            const estrella = parseInt(boton.dataset.estrella, 10);
            // comprueba si el ratón está en la mitad izquierda o derecha
            const rect = boton.getBoundingClientRect();
            const mitadIzquierda = e.clientX - rect.left < rect.width / 2;
            // mitad izquierda = media, mitad derecha = completa
            const valor = mitadIzquierda ? (estrella * VALOR_ESTRELLA) - VALOR_MEDIA_ESTRELLA : estrella * VALOR_ESTRELLA;

            pintar(valor, false);
        });

        // al hacer click guarda el valor elegido
        boton.addEventListener('click', function(e) {
            const estrella = parseInt(boton.dataset.estrella, 10);
            const rect = boton.getBoundingClientRect();
            const mitadIzquierda = e.clientX - rect.left < rect.width / 2;

            guardar(String(mitadIzquierda ? (estrella * VALOR_ESTRELLA) - VALOR_MEDIA_ESTRELLA : estrella * VALOR_ESTRELLA));
        });

    });

    selector.addEventListener('mouseleave', function() {
        pintar(inputPuntuacion.value);
    });

    if (limpiar) {
        limpiar.addEventListener('click', function() {
            guardar('');
        });
    }

    pintar(inputPuntuacion.value);

    return {
        pintar: pintar
    };
}
