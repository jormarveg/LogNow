const favoritoForm = $('.favorito-form');
const estadoForms = $('.estado-form');

if (favoritoForm.length) {
    favoritoForm.on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const boton = form.find('.favorito-juego');
        const icono = boton.find('i');
        const inputFavorito = form.find('input[name="favorito"]');
        const idVideojuego = form.find('input[name="id_videojuego"]').val();
        const favoritoActual = inputFavorito.val() === '1';
        const nuevoFavorito = favoritoActual ? 0 : 1;

        if (boton.hasClass('cargando')) {
            return;
        }

        boton.addClass('cargando');

        $.post('/ajax/toggle-favorito.php', {
            id_videojuego: idVideojuego,
            favorito: nuevoFavorito
        }).done(function(respuesta) {
            if (!respuesta || !respuesta.ok) {
                return;
            }

            inputFavorito.val(respuesta.favorito ? '1' : '0');
            boton.toggleClass('active', respuesta.favorito);
            icono.toggleClass('fa-solid', respuesta.favorito);
            icono.toggleClass('fa-regular', !respuesta.favorito);
            boton.attr('aria-label', respuesta.favorito ? 'Quitar de favoritos' : 'Marcar como favorito');

            boton
                .stop(true, true)
                .animate({ opacity: 0.45 }, 80)
                .animate({ opacity: 1 }, 180);

            boton.addClass('pulso');
            window.setTimeout(function() {
                boton.removeClass('pulso');
            }, 260);
        }).always(function() {
            boton.removeClass('cargando');
        });
    });
}

if (estadoForms.length) {
    estadoForms.on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const boton = form.find('.estado-boton');
        const idVideojuego = form.find('input[name="id_videojuego"]').val();
        const estado = form.find('input[name="estado_juego"]').val();

        if (boton.hasClass('cargando') || boton.hasClass('active')) {
            return;
        }

        boton.addClass('cargando');

        $.post('/ajax/cambiar-estado.php', {
            id_videojuego: idVideojuego,
            estado: estado
        }).done(function(respuesta) {
            if (!respuesta || !respuesta.ok) {
                return;
            }

            estadoForms.find('.estado-boton').removeClass('active');
            boton.addClass('active');

            boton
                .stop(true, true)
                .animate({ opacity: 0.45 }, 80)
                .animate({ opacity: 1 }, 180);

            boton.addClass('pulso');
            window.setTimeout(function() {
                boton.removeClass('pulso');
            }, 260);
        }).always(function() {
            boton.removeClass('cargando');
        });
    });
}
