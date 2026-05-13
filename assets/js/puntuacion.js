function iniciarSelectorPuntuacion(opciones) {
    const selector = document.getElementById(opciones.selectorId || 'selector-puntuacion');
    const input = document.getElementById(opciones.inputId || 'puntuacion');
    const texto = document.getElementById(opciones.textoId || 'texto-puntuacion');
    const limpiar = document.getElementById(opciones.limpiarId || 'limpiar-puntuacion');
    const botones = selector ? selector.querySelectorAll('.estrella-puntuacion[data-estrella]') : [];
    const alCambiar = typeof opciones.alCambiar === 'function' ? opciones.alCambiar : function() {};

    if (!selector || !input || !texto || botones.length === 0) {
        return null;
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

    function pintar(valor) {
        const numero = parseInt(valor || '0', 10);

        texto.textContent = textoEstrellas(numero);

        botones.forEach(function(boton) {
            const estrella = parseInt(boton.dataset.estrella, 10);
            const icono = boton.querySelector('i');

            icono.className = iconoPuntuacion(estrella, numero);
            boton.classList.toggle('activa', numero >= (estrella * 20) - 10);
        });
    }

    function guardar(valor) {
        input.value = valor;
        pintar(valor);
        alCambiar(input);
    }

    botones.forEach(function(boton) {
        boton.addEventListener('mousemove', function(e) {
            const estrella = parseInt(boton.dataset.estrella, 10);
            const rect = boton.getBoundingClientRect();
            const mitadIzquierda = e.clientX - rect.left < rect.width / 2;
            const valor = mitadIzquierda ? (estrella * 20) - 10 : estrella * 20;

            pintar(valor);
        });

        boton.addEventListener('focus', function() {
            pintar(parseInt(boton.dataset.estrella, 10) * 20);
        });

        boton.addEventListener('click', function(e) {
            const estrella = parseInt(boton.dataset.estrella, 10);
            const rect = boton.getBoundingClientRect();
            const mitadIzquierda = e.clientX - rect.left < rect.width / 2;

            guardar(String(mitadIzquierda ? (estrella * 20) - 10 : estrella * 20));
        });

        boton.addEventListener('keydown', function(e) {
            const estrella = parseInt(boton.dataset.estrella, 10);

            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                guardar(String(estrella * 20));
            }
        });
    });

    selector.addEventListener('mouseleave', function() {
        pintar(input.value);
    });

    if (limpiar) {
        limpiar.addEventListener('click', function() {
            guardar('');
        });
    }

    pintar(input.value);

    return {
        pintar: pintar
    };
}
