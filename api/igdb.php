<?php

function igdbClientId() {
    $clientId = getenv('TWITCH_CLIENT_ID');

    if ($clientId === false) {
        return '';
    }

    return trim($clientId);
}

function igdbClientSecret() {
    $clientSecret = getenv('TWITCH_CLIENT_SECRET');

    if ($clientSecret === false) {
        return '';
    }

    return trim($clientSecret);
}

function igdbDisponible() {
    return igdbClientId() !== '' && igdbClientSecret() !== '';
}

function igdbTokenCacheFile() {
    return '/tmp/lognow_igdb_token.json';
}

function igdbLeerTokenCache() {
    $ruta = igdbTokenCacheFile();

    if (!is_file($ruta)) {
        return null;
    }

    $contenido = @file_get_contents($ruta);

    if ($contenido === false) {
        return null;
    }

    $datos = json_decode($contenido, true);

    return is_array($datos) ? $datos : null;
}

function igdbGuardarTokenCache($token, $expiraEn) {
    $datos = [
        'access_token' => $token,
        'expires_at' => time() + max(0, (int) $expiraEn) - 300
    ];

    @file_put_contents(igdbTokenCacheFile(), json_encode($datos));
}

function igdbObtenerToken() {
    if (!igdbDisponible()) {
        return '';
    }

    $cache = igdbLeerTokenCache();

    if ($cache && !empty($cache['access_token']) && !empty($cache['expires_at']) && (int) $cache['expires_at'] > time()) {
        return $cache['access_token'];
    }

    $respuesta = @file_get_contents('https://id.twitch.tv/oauth2/token', false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query([
                'client_id' => igdbClientId(),
                'client_secret' => igdbClientSecret(),
                'grant_type' => 'client_credentials'
            ]),
            'timeout' => 8,
            'ignore_errors' => true
        ]
    ]));

    if ($respuesta === false) {
        return '';
    }

    $datos = json_decode($respuesta, true);

    if (!is_array($datos) || empty($datos['access_token'])) {
        return '';
    }

    igdbGuardarTokenCache($datos['access_token'], $datos['expires_in'] ?? 0);

    return $datos['access_token'];
}

function igdbCamposJuego() {
    return 'fields id,name,first_release_date,summary,cover.image_id,cover.url,artworks.image_id,artworks.url,screenshots.image_id,screenshots.url,genres.id,genres.name,platforms.id,platforms.name,involved_companies.company.id,involved_companies.company.name,involved_companies.developer;';
}

function igdbEscaparTexto($texto) {
    return str_replace(['\\', '"'], ['\\\\', '\\"'], trim($texto));
}

function igdbRequest($endpoint, $consulta) {
    $token = igdbObtenerToken();

    if ($token === '') {
        return null;
    }

    $respuesta = @file_get_contents('https://api.igdb.com/v4/' . ltrim($endpoint, '/'), false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Client-ID: " . igdbClientId() . "\r\n"
                . "Authorization: Bearer " . $token . "\r\n"
                . "Accept: application/json\r\n"
                . "Content-Type: text/plain\r\n",
            'content' => $consulta,
            'timeout' => 8,
            'ignore_errors' => true
        ]
    ]));

    if ($respuesta === false) {
        return null;
    }

    $datos = json_decode($respuesta, true);

    return is_array($datos) ? $datos : null;
}

function igdbUrlImagen($url, $tamano = null) {
    if (!$url) {
        return null;
    }

    if (str_starts_with($url, '//')) {
        $url = 'https:' . $url;
    }

    if ($tamano) {
        return preg_replace('#/t_[^/]+/#', '/' . $tamano . '/', $url, 1);
    }

    return $url;
}

function igdbUrlDesdeImageId($imageId, $tamano = 't_cover_big') {
    $imageId = trim((string) $imageId);

    if ($imageId === '') {
        return null;
    }

    return 'https://images.igdb.com/igdb/image/upload/' . $tamano . '/' . $imageId . '.jpg';
}

function igdbPortadaJuego($cover) {
    if (!is_array($cover)) {
        return null;
    }

    if (!empty($cover['url'])) {
        return igdbUrlImagen($cover['url'], 't_cover_big');
    }

    if (!empty($cover['image_id'])) {
        return igdbUrlDesdeImageId($cover['image_id'], 't_cover_big');
    }

    return null;
}

function igdbBackgroundJuego($juego) {
    $artwork = $juego['artworks'][0] ?? null;

    if (is_array($artwork)) {
        if (!empty($artwork['url'])) {
            return igdbUrlImagen($artwork['url']);
        }

        if (!empty($artwork['image_id'])) {
            return igdbUrlDesdeImageId($artwork['image_id'], 't_screenshot_big');
        }
    }

    $screenshot = $juego['screenshots'][0] ?? null;

    if (is_array($screenshot)) {
        if (!empty($screenshot['url'])) {
            return igdbUrlImagen($screenshot['url']);
        }

        if (!empty($screenshot['image_id'])) {
            return igdbUrlDesdeImageId($screenshot['image_id'], 't_screenshot_big');
        }
    }

    return igdbPortadaJuego($juego['cover'] ?? null);
}

function igdbPopulares($pagina = 1, $tamano = 20) {
    $pagina = max(1, (int) $pagina);
    $tamano = max(1, min(50, (int) $tamano));
    $offset = ($pagina - 1) * $tamano;

    return igdbRequest('games', igdbCamposJuego()
        . ' where total_rating_count != null & cover != null & first_release_date != null;'
        . ' sort total_rating_count desc;'
        . ' limit ' . $tamano . ';'
        . ' offset ' . $offset . ';');
}

function igdbBuscarJuegos($busqueda, $pagina = 1, $tamano = 20) {
    $busqueda = igdbEscaparTexto($busqueda);

    if ($busqueda === '') {
        return [];
    }

    $pagina = max(1, (int) $pagina);
    $tamano = max(1, min(50, (int) $tamano));
    $offset = ($pagina - 1) * $tamano;

    return igdbRequest('games', igdbCamposJuego()
        . ' search "' . $busqueda . '";'
        . ' where category = 0 & version_parent = null;'
        . ' limit ' . $tamano . ';'
        . ' offset ' . $offset . ';');
}

function igdbObtenerJuego($idIgdb) {
    $idIgdb = (int) $idIgdb;

    if ($idIgdb <= 0) {
        return null;
    }

    $respuesta = igdbRequest('games', igdbCamposJuego()
        . ' where id = ' . $idIgdb . ';'
        . ' limit 1;');

    if (!$respuesta || empty($respuesta[0])) {
        return null;
    }

    return $respuesta[0];
}
