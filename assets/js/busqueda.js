const paginaBusqueda = $('.busqueda-page');

if (paginaBusqueda.length) {
    const minimoBusqueda = Number(paginaBusqueda.data('busqueda-minima')) || 5;
    const resumenBusqueda = $('#resumen-busqueda');
    const estadoBusqueda = $('#estado-busqueda-ajax');
    const avisoBusqueda = $('#aviso-busqueda');
    const resultadosBusqueda = $('#resultados-busqueda');
    const paginacionBusqueda = $('#paginacion-busqueda');
    const contenidoBusqueda = $('#busqueda-contenido');
    const formImportarIgdb = $('#form-importar-igdb');
    const inputImportarIgdb = formImportarIgdb.find('input[name="q"]');
    const formulariosBusqueda = $('.buscar, .formulario-busqueda-movil');
    const inputsBusqueda = $('input[name="q"]');
    const tiempoMaximoBusqueda = 10000;
    let solicitudLocal = null;
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
        inputImportarIgdb.val(respuesta.busqueda);
        formImportarIgdb.prop('hidden', textoLimpio(respuesta.busqueda).length < minimoBusqueda);
    }

    function terminarBusqueda() {
        contenidoBusqueda.removeClass('cargando');
    }

    function cargarBusquedaLocal(busqueda, pagina) {
        if (solicitudLocal) {
            solicitudLocal.abort();
            solicitudLocal = null;
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
                p: pagina
            }
        }).done(function(respuesta) {
            if (!respuesta || !respuesta.ok || claveBusquedaActiva !== clave) {
                return;
            }

            aplicarRespuestaBusqueda(respuesta);

            actualizarUrlBusqueda(busqueda, respuesta.pagina_actual);
            limpiarEstadoBusqueda();
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
            limpiarEstadoBusqueda();
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
}
