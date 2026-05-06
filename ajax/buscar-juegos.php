<?php
require '../api/cache.php';
require '../includes/auth.php';
require '../includes/busqueda_helpers.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Metodo no permitido'
    ]);
    exit;
}

$busqueda = trim($_GET['q'] ?? '');
$paginaActual = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$modo = $_GET['modo'] ?? 'local';

if (!in_array($modo, ['local', 'igdb'], true)) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Modo no valido'
    ]);
    exit;
}

$datosBusqueda = datosBusquedaLocal($db, $busqueda, $paginaActual, BUSQUEDA_POR_PAGINA);
$aviso = '';
$claseAviso = '';

if ($busqueda !== '' && $datosBusqueda['longitud'] < BUSQUEDA_MINIMA_JUEGOS) {
    $aviso = 'Escribe al menos ' . BUSQUEDA_MINIMA_JUEGOS . ' caracteres para buscar.';
}

if ($modo === 'igdb' && $busqueda !== '' && $datosBusqueda['longitud'] >= BUSQUEDA_MINIMA_JUEGOS && $paginaActual === 1) {
    $resultadoIgdb = cacheImportarBusquedaIgdb($db, $busqueda, 1, BUSQUEDA_POR_PAGINA);

    if ($resultadoIgdb['ok']) {
        $datosBusqueda = datosBusquedaLocal($db, $busqueda, 1, BUSQUEDA_POR_PAGINA);
        $paginaActual = $datosBusqueda['pagina_actual'];
    } elseif ($datosBusqueda['total_juegos'] === 0) {
        if ($resultadoIgdb['mensaje'] === 'No hay credenciales de IGDB configuradas') {
            $aviso = 'No hay resultados guardados y la búsqueda externa no está disponible ahora mismo.';
        } else {
            $aviso = 'No se ha podido completar la búsqueda en este momento.';
        }

        $claseAviso = ' aviso-igdb';
    }
}

echo json_encode([
    'ok' => true,
    'busqueda' => $busqueda,
    'pagina_actual' => $datosBusqueda['pagina_actual'],
    'total_juegos' => $datosBusqueda['total_juegos'],
    'total_paginas' => $datosBusqueda['total_paginas'],
    'resumen' => textoResumenBusqueda($busqueda, $datosBusqueda['total_juegos']),
    'html_resultados' => htmlResultadosBusqueda($datosBusqueda['juegos'], $busqueda, $datosBusqueda['longitud']),
    'html_paginacion' => htmlPaginacionBusqueda($busqueda, $datosBusqueda['pagina_actual'], $datosBusqueda['total_paginas']),
    'aviso' => $aviso,
    'clase_aviso' => $claseAviso
]);
