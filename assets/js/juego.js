const favoritoForm = $('.favorito-form');
const estadoForms = $('.estado-form');
const modalReporte = $('#modalReporte');
const modalEliminarResenaAdmin = $('#modalEliminarResenaAdmin');
const formReporte = $('#formReporte');
const formPuntuacionJuego = $('.form-puntuacion-juego');
let botonReporteActivo = null;
let formEliminarResenaActivo = null;
let puntuacionGuardada = $('#puntuacion').val() || '';
let selectorPuntuacionJuego = null;

selectorPuntuacionJuego = iniciarSelectorPuntuacion({
    alCambiar: function(input) {
        if (!formPuntuacionJuego.length) {
            return;
        }

        guardarPuntuacionJuego(input.value);
    }
});

function guardarPuntuacionJuego(puntuacion) {
    const mensaje = formPuntuacionJuego.find('.mensaje-puntuacion-juego');
    const idVideojuego = formPuntuacionJuego.find('input[name="id_videojuego"]').val();

    if (puntuacion === puntuacionGuardada) {
        return;
    }

    if (formPuntuacionJuego.hasClass('cargando')) {
        return;
    }

    formPuntuacionJuego.addClass('cargando');
    mensaje.removeClass('ok error').text('Guardando...');

    $.post('/ajax/puntuar-juego.php', {
        id_videojuego: idVideojuego,
        puntuacion: puntuacion
    }).done(function(respuesta) {
        if (!respuesta || !respuesta.ok) {
            mensaje.addClass('error').text('No se ha podido guardar.');
            return;
        }

        puntuacionGuardada = respuesta.puntuacion;
        $('#puntuacion').val(respuesta.puntuacion);

        if (selectorPuntuacionJuego) {
            selectorPuntuacionJuego.pintar(respuesta.puntuacion);
        }

        actualizarPuntuacionVisible(respuesta);
        mensaje.removeClass('ok error').text('');

        if (respuesta.creado) {
            window.setTimeout(function() {
                window.location.reload();
            }, 300);
        }
    }).fail(function(xhr) {
        const respuesta = xhr.responseJSON || {};
        $('#puntuacion').val(puntuacionGuardada);

        if (selectorPuntuacionJuego) {
            selectorPuntuacionJuego.pintar(puntuacionGuardada);
        }

        mensaje.addClass('error').text(respuesta.mensaje || 'No se ha podido guardar.');
    }).always(function() {
        formPuntuacionJuego.removeClass('cargando');
    });
}

function actualizarPuntuacionVisible(respuesta) {
    const caja = formPuntuacionJuego.closest('.tu-puntuacion');
    let numero = caja.find('.numero-puntuacion');

    if (respuesta.puntuacion === '') {
        numero.remove();
        return;
    }

    if (!numero.length) {
        numero = $('<p class="numero-puntuacion"></p>');
        caja.find('h2').after(numero);
    }

    numero.text(respuesta.puntuacion_visible);
}

if (formPuntuacionJuego.length) {
    formPuntuacionJuego.on('submit', function(e) {
        e.preventDefault();
    });
}

$('.boton-leer-resena').on('click', function() {
    const boton = $(this);
    const resena = boton.closest('.elemento-carousel');
    const expandida = !resena.hasClass('resena-expandida');

    resena.toggleClass('resena-expandida', expandida);
    boton
        .attr('aria-expanded', expandida ? 'true' : 'false')
        .text(expandida ? 'Leer menos' : 'Leer más');
});

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
        }).fail(function(xhr) {
            if (xhr.responseJSON && xhr.responseJSON.mensaje) {
                window.alert(xhr.responseJSON.mensaje);
            }
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

            if (respuesta.creado) {
                window.location.reload();
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

if (modalReporte.length) {
    const idResenaReporte = $('#idResenaReporte');
    const motivoReporte = $('#motivoReporte');
    const mensajeReporte = $('#mensajeReporte');
    const botonEnviarReporte = formReporte.find('.boton-enviar-reporte');

    function abrirModalReporte(boton) {
        botonReporteActivo = boton;
        idResenaReporte.val(boton.attr('data-id-resena'));
        motivoReporte.val('');
        mensajeReporte.removeClass('ok error').text('');
        botonEnviarReporte.prop('disabled', false).text('Reportar');
        modalReporte
            .stop(true, true)
            .prop('hidden', false)
            .css('display', 'grid')
            .hide()
            .fadeIn(160, function() {
                motivoReporte.trigger('focus');
            });
    }

    function cerrarModalReporte() {
        modalReporte
            .stop(true, true)
            .fadeOut(140, function() {
                modalReporte.prop('hidden', true).css('display', '');
                botonReporteActivo = null;
            });
    }

    $('.boton-reportar-resena[data-id-resena]').on('click', function(e) {
        e.preventDefault();
        abrirModalReporte($(this));
    });

    modalReporte.find('.boton-cancelar-reporte, .modal-reporte-fondo').on('click', function() {
        cerrarModalReporte();
    });

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && !modalReporte.prop('hidden')) {
            cerrarModalReporte();
        }
    });

    formReporte.on('submit', function(e) {
        e.preventDefault();

        const motivo = motivoReporte.val().trim();

        if (motivo.length < 5 || motivo.length > 255) {
            mensajeReporte.removeClass('ok').addClass('error').text('Escribe un motivo entre 5 y 255 caracteres.');
            return;
        }

        botonEnviarReporte.prop('disabled', true).text('Enviando...');
        mensajeReporte.removeClass('ok error').text('');

        $.post('/ajax/reportar.php', {
            id_resena: idResenaReporte.val(),
            motivo: motivo
        }).done(function(respuesta) {
            if (!respuesta || !respuesta.ok) {
                mensajeReporte.addClass('error').text('No se ha podido enviar el reporte.');
                botonEnviarReporte.prop('disabled', false).text('Reportar');
                return;
            }

            mensajeReporte.addClass('ok').text(respuesta.mensaje);

            if (botonReporteActivo) {
                botonReporteActivo.prop('disabled', true).text('Reportado');
            }

            window.setTimeout(function() {
                cerrarModalReporte();
            }, 900);
        }).fail(function(xhr) {
            const mensaje = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No se ha podido enviar el reporte.';
            mensajeReporte.addClass('error').text(mensaje);
            botonEnviarReporte.prop('disabled', false).text('Reportar');
        });
    });
}

if (modalEliminarResenaAdmin.length) {
    const botonConfirmarEliminar = modalEliminarResenaAdmin.find('.boton-confirmar-eliminar-resena-admin');

    function abrirModalEliminarResena(form) {
        formEliminarResenaActivo = form;
        modalEliminarResenaAdmin
            .stop(true, true)
            .prop('hidden', false)
            .css('display', 'grid')
            .hide()
            .fadeIn(160, function() {
                botonConfirmarEliminar.trigger('focus');
            });
    }

    function cerrarModalEliminarResena() {
        modalEliminarResenaAdmin
            .stop(true, true)
            .fadeOut(140, function() {
                modalEliminarResenaAdmin.prop('hidden', true).css('display', '');
                formEliminarResenaActivo = null;
            });
    }

    $('.abrir-modal-eliminar-resena-admin').on('click', function(e) {
        e.preventDefault();
        abrirModalEliminarResena($(this).closest('form'));
    });

    modalEliminarResenaAdmin.find('.boton-cancelar-reporte, .modal-reporte-fondo').on('click', function() {
        cerrarModalEliminarResena();
    });

    botonConfirmarEliminar.on('click', function() {
        if (formEliminarResenaActivo) {
            formEliminarResenaActivo.trigger('submit');
        }
    });

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && !modalEliminarResenaAdmin.prop('hidden')) {
            cerrarModalEliminarResena();
        }
    });
}
