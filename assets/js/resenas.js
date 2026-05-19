document.querySelectorAll('.boton-leer-resena-perfil').forEach(function(boton) {
    boton.addEventListener('click', function() {
        const texto = boton.closest('.texto');
        const puntos = texto.querySelector('.puntos-resena');
        const resto = texto.querySelector('.texto-resto-resena');
        const expandida = boton.getAttribute('aria-expanded') !== 'true';

        if (!puntos || !resto) {
            return;
        }

        puntos.hidden = expandida;
        resto.hidden = !expandida;
        boton.setAttribute('aria-expanded', expandida ? 'true' : 'false');
        boton.textContent = expandida ? 'Leer menos' : 'Leer más';
    });
});
