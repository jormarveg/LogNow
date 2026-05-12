<?php

const BUSQUEDA_MINIMA_JUEGOS = 5;
const BUSQUEDA_POR_PAGINA = 18;

function urlBusqueda($busqueda, $pagina = 1) {
    $params = [];

    if ($busqueda !== '') {
        $params['q'] = $busqueda;
    }

    if ((int) $pagina > 1) {
        $params['p'] = (int) $pagina;
    }

    $query = http_build_query($params);

    return '/buscar.php' . ($query ? '?' . $query : '');
}

function paginasBusqueda($paginaActual, $totalPaginas) {
    if ($totalPaginas <= 7) {
        return range(1, $totalPaginas);
    }

    $paginas = [1];
    $inicio = max(2, $paginaActual - 1);
    $fin = min($totalPaginas - 1, $paginaActual + 1);

    if ($inicio > 2) {
        $paginas[] = '...';
    }

    for ($i = $inicio; $i <= $fin; $i++) {
        $paginas[] = $i;
    }

    if ($fin < $totalPaginas - 1) {
        $paginas[] = '...';
    }

    $paginas[] = $totalPaginas;

    return $paginas;
}

function datosBusquedaLocal(PDO $db, $busqueda, $paginaActual = 1, $porPagina = BUSQUEDA_POR_PAGINA) {
    $busqueda = trim((string) $busqueda);
    $longitud = mb_strlen($busqueda, 'UTF-8');
    $paginaActual = max(1, (int) $paginaActual);
    $porPagina = max(1, (int) $porPagina);

    if ($busqueda === '' || $longitud < BUSQUEDA_MINIMA_JUEGOS) {
        return [
            'busqueda' => $busqueda,
            'longitud' => $longitud,
            'pagina_actual' => 1,
            'por_pagina' => $porPagina,
            'total_juegos' => 0,
            'total_paginas' => 1,
            'juegos' => []
        ];
    }

    $totalJuegos = cacheContarBusquedaLocal($db, $busqueda);
    $totalPaginas = max(1, (int) ceil($totalJuegos / $porPagina));

    if ($paginaActual > $totalPaginas) {
        $paginaActual = $totalPaginas;
    }

    $offset = ($paginaActual - 1) * $porPagina;

    return [
        'busqueda' => $busqueda,
        'longitud' => $longitud,
        'pagina_actual' => $paginaActual,
        'por_pagina' => $porPagina,
        'total_juegos' => $totalJuegos,
        'total_paginas' => $totalPaginas,
        'juegos' => cacheBuscarJuegosLocal($db, $busqueda, $porPagina, $offset)
    ];
}

function textoResumenBusqueda($busqueda, $totalJuegos) {
    $busqueda = trim((string) $busqueda);

    if ($busqueda === '') {
        return 'Busca cualquier juego por su nombre.';
    }

    return number_format((int) $totalJuegos, 0, ',', '.') . ' resultados para "' . $busqueda . '"';
}

function htmlResultadosBusqueda($juegos, $busqueda, $longitud) {
    ob_start();

    if ($juegos):
        foreach ($juegos as $juego): ?>
            <a class="juego" href="/juego.php?id=<?= (int) $juego['igdb_id'] ?>">
                <div class="portada">
                    <img src="<?= htmlspecialchars(urlPortadaJuego($juego['portada_url'] ?? '', $juego['titulo'])) ?>" alt="Portada de <?= htmlspecialchars($juego['titulo']) ?>">
                    <div class="titulo">
                        <p><?= htmlspecialchars($juego['titulo']) ?></p>
                    </div>
                </div>
                <div class="puntuacion puntuacion-<?= htmlspecialchars($juego['origen_puntuacion']) ?>">
                    <i class="fa-solid fa-star"></i>
                    <span><?= $juego['puntuacion_visible'] !== null ? number_format((float) $juego['puntuacion_visible'], 1) : 'N/D' ?></span>
                    <?php if ($juego['origen_puntuacion'] === 'igdb'): ?>
                        <small>IGDB</small>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach;
    elseif ($busqueda !== '' && $longitud >= BUSQUEDA_MINIMA_JUEGOS): ?>
        <div class="sin-resultados">
            <p>No se ha encontrado ningún juego con ese nombre.</p>
        </div>
    <?php elseif ($busqueda !== ''): ?>
        <div class="sin-resultados">
            <p>Escribe al menos <?= BUSQUEDA_MINIMA_JUEGOS ?> caracteres para buscar.</p>
        </div>
    <?php else: ?>
        <div class="sin-resultados">
            <p>Escribe el nombre de un juego para buscar.</p>
        </div>
    <?php endif;

    return trim((string) ob_get_clean());
}

function htmlPaginacionBusqueda($busqueda, $paginaActual, $totalPaginas) {
    if ($totalPaginas <= 1) {
        return '';
    }

    ob_start(); ?>
    <nav class="paginacion">
        <?php if ($paginaActual > 1): ?>
            <a href="<?= htmlspecialchars(urlBusqueda($busqueda, $paginaActual - 1)) ?>">Anterior</a>
        <?php endif; ?>

        <?php foreach (paginasBusqueda($paginaActual, $totalPaginas) as $item): ?>
            <?php if ($item === '...'): ?>
                <span class="separador">...</span>
            <?php else: ?>
                <a href="<?= htmlspecialchars(urlBusqueda($busqueda, $item)) ?>"<?= $item === $paginaActual ? ' class="active"' : '' ?>><?= $item ?></a>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($paginaActual < $totalPaginas): ?>
            <a href="<?= htmlspecialchars(urlBusqueda($busqueda, $paginaActual + 1)) ?>">Siguiente</a>
        <?php endif; ?>
    </nav>
    <?php

    return trim((string) ob_get_clean());
}
