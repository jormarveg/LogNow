const paginaBusqueda = $('.busqueda-page');

if (paginaBusqueda.length) {
    const minimoBusqueda = Number(paginaBusqueda.data('busqueda-minima')) || 5;
    const resumenBusqueda = $('#resumen-busqueda');
    const estadoBusqueda = $('#estado-busqueda-ajax');
    const avisoBusqueda = $('#aviso-busqueda');
    const resultadosBusqueda = $('#resultados-busqueda');
    const paginacionBusqueda = $('#paginacion-busqueda');
    const contenidoBusqueda = $('#busqueda-contenido');
    const formulariosBusqueda = $('.buscar, .formulario-busqueda-movil');
    const inputsBusqueda = $('input[name="q"]');
    const tiempoMaximoBusqueda = 10000;
    let solicitudLocal = null;
    let solicitudIgdb = null;
    let claveBusquedaActiva = '';

    function textoLimpio(texto) {
        return $.trim(texto || '');
    }

    function sincronizarInputs(valor) {
        inputsBusqueda.val(valor);
    }

    function limpiarEstadoBusqueda() {
        estadoBusqueda.text('');
    }

    function limpiarEstadoSiNoHayPeticiones() {
        if (!solicitudLocal && !solicitudIgdb) {
            limpiarEstadoBusqueda();
        }
    }

    function actualizarUrlBusqueda(busqueda, pagina, reemplazar = false) {
        const params = new URLSearchParams();

        if (busqueda !== '') {
            params.set('q', busqueda);
        }

        if (pagina > 1) {
            params.set('p', String(pagina));
        }

        const url = '/buscar.php' + (params.toString() ? '?' . params.toString() : '');
        const metodo = reemplazar ? 'replaceState' : 'pushState';
        window.history[metodo]({ q: busqueda, p: pagina }, '', url);
    }

    function mostrarAvisoBusqueda(texto, clase = '') {
        avisoBusqueda.attr('class', 'aviso-busqueda' + clase);

        if (textoLimpio(texto) === '') {
            avisoBusqueda.prop('hidden', true);
            avisoBusqueda.find('p').text('');
            return;
        }

        avisoBusqueda.find('p').text(texto);
        avisoBusqueda.prop('hidden', false);
    }

    function actualizarBloque($bloque, html) {
        const actual = textoLimpio($bloque.html());
        const nuevo = textoLimpio(html);

        if (actual === nuevo) {
            return;
        }

        $bloque
            .stop(true, true)
            .fadeTo(90, 0.2, function() {
                $bloque.html(html).fadeTo(180, 1);
            });
    }

    function aplicarRespuestaBusqueda(respuesta) {
        resumenBusqueda.text(respuesta.resumen);
        actualizarBloque(resultadosBusqueda, respuesta.html_resultados);
        actualizarBloque(paginacionBusqueda, respuesta.html_paginacion);
        mostrarAvisoBusqueda(respuesta.aviso, respuesta.clase_aviso || '');
        paginaBusqueda.attr('data-busqueda-pagina', String(respuesta.pagina_actual));
    }

    function terminarBusqueda() {
        contenidoBusqueda.removeClass('cargando');
    }

    function cargarBusquedaIgdb(busqueda, claveEsperada) {
        if (solicitudIgdb) {
            solicitudIgdb.abort();
        }

        solicitudIgdb = $.ajax({
            url: '/ajax/buscar-juegos.php',
            dataType: 'json',
            timeout: tiempoMaximoBusqueda,
            data: {
                q: busqueda,
                p: 1,
                modo: 'igdb'
            }
        }).done(function(respuesta) {
            if (!respuesta || !respuesta.ok || claveBusquedaActiva !== claveEsperada) {
                return;
            }

            aplicarRespuestaBusqueda(respuesta);
        }).fail(function() {
            if (claveBusquedaActiva !== claveEsperada) {
                return;
            }

            console.error('IGDB no responde');
            limpiarEstadoSiNoHayPeticiones();
        }).always(function() {
            solicitudIgdb = null;
            limpiarEstadoSiNoHayPeticiones();
        });
    }

    function cargarBusquedaLocal(busqueda, pagina) {
        if (solicitudLocal) {
            solicitudLocal.abort();
            solicitudLocal = null;
        }

        if (solicitudIgdb) {
            solicitudIgdb.abort();
            solicitudIgdb = null;
        }

        const clave = busqueda + '|' + pagina;

        claveBusquedaActiva = clave;
        sincronizarInputs(busqueda);
        limpiarEstadoBusqueda();
        contenidoBusqueda.addClass('cargando');

        solicitudLocal = $.ajax({
            url: '/ajax/buscar-juegos.php',
            dataType: 'json',
            timeout: tiempoMaximoBusqueda,
            data: {
                q: busqueda,
                p: pagina,
                modo: 'local'
            }
        }).done(function(respuesta) {
            if (!respuesta || !respuesta.ok || claveBusquedaActiva !== clave) {
                return;
            }

            aplicarRespuestaBusqueda(respuesta);

            actualizarUrlBusqueda(busqueda, respuesta.pagina_actual);

            if (textoLimpio(busqueda) !== '' && busqueda.length >= minimoBusqueda && respuesta.pagina_actual === 1) {
                cargarBusquedaIgdb(busqueda, clave);
            } else {
                limpiarEstadoBusqueda();
            }
        }).fail(function() {
            if (claveBusquedaActiva !== clave) {
                return;
            }

            mostrarAvisoBusqueda('No se ha podido completar la búsqueda en este momento.');
            limpiarEstadoBusqueda();
        }).always(function() {
            solicitudLocal = null;
            if (claveBusquedaActiva === clave) {
                terminarBusqueda();
            }
            limpiarEstadoSiNoHayPeticiones();
        });
    }

    formulariosBusqueda.on('submit', function(e) {
        const busqueda = textoLimpio($(this).find('input[name="q"]').val());

        if ($(this).is('.buscar') || $(this).is('.formulario-busqueda-movil')) {
            e.preventDefault();
            cargarBusquedaLocal(busqueda, 1);
        }
    });

    const busquedaInicial = textoLimpio($('form.buscar input[name="q"]').val() || '');
    const paginaInicial = Number(paginaBusqueda.data('busqueda-pagina')) || 1;

    claveBusquedaActiva = busquedaInicial + '|' + paginaInicial;

    if (busquedaInicial !== '' && busquedaInicial.length >= minimoBusqueda && paginaInicial === 1) {
        cargarBusquedaIgdb(busquedaInicial, claveBusquedaActiva);
    }
}
