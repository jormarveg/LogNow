<?php

function rawgApiKey() {
    $key = getenv('RAWG_API_KEY');

    if ($key === false) {
        return '';
    }

    return trim($key);
}

function rawgDisponible() {
    return rawgApiKey() !== '';
}

function rawgRequest($endpoint, $params = []) {
    if (!rawgDisponible()) {
        return null;
    }

    $params['key'] = rawgApiKey();
    $url = 'https://api.rawg.io/api/' . ltrim($endpoint, '/');
    $url .= '?' . http_build_query($params);

    $contexto = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 6,
            'ignore_errors' => true
        ]
    ]);

    $respuesta = @file_get_contents($url, false, $contexto);

    if ($respuesta === false) {
        return null;
    }

    $datos = json_decode($respuesta, true);

    if (!is_array($datos)) {
        return null;
    }

    return $datos;
}

function rawgPopulares($pagina = 1, $tamano = 20) {
    return rawgRequest('games', [
        'page' => max(1, (int) $pagina),
        'page_size' => max(1, min(40, (int) $tamano)),
        'ordering' => '-rating'
    ]);
}

function rawgBuscarJuegos($busqueda, $pagina = 1, $tamano = 20) {
    $busqueda = trim($busqueda);

    if ($busqueda === '') {
        return [
            'results' => []
        ];
    }

    return rawgRequest('games', [
        'search' => $busqueda,
        'page' => max(1, (int) $pagina),
        'page_size' => max(1, min(40, (int) $tamano))
    ]);
}

function rawgObtenerJuego($idRawg) {
    $idRawg = (int) $idRawg;

    if ($idRawg <= 0) {
        return null;
    }

    return rawgRequest('games/' . $idRawg);
}
