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

if (isset($_GET['modo']) && $_GET['modo'] !== 'local') {
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
