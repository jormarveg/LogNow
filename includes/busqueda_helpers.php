<?php

const BUSQUEDA_MINIMA_JUEGOS = 3;
const BUSQUEDA_POR_PAGINA = 18;
const BUSQUEDA_USUARIOS_POR_PAGINA = 12;

function tipoBusquedaValido($tipo) {
    return in_array($tipo, ['juegos', 'usuarios'], true) ? $tipo : 'juegos';
}

function urlBusqueda($busqueda, $pagina = 1, $tipo = 'juegos') {
    $tipo = tipoBusquedaValido($tipo);
    $params = [];

    if ($busqueda !== '') {
        $params['q'] = $busqueda;
    }

    if ($tipo !== 'juegos') {
        $params['tipo'] = $tipo;
    }

    if ((int) $pagina > 1) {
        $params['p'] = (int) $pagina;
    }

    $query = http_build_query($params);

    return '/buscar.php' . ($query ? '?' . $query : '');
}

function urlTabBusqueda($busqueda, $tipo) {
    $tipo = tipoBusquedaValido($tipo);
    $params = ['tipo' => $tipo];

    if ($busqueda !== '') {
        $params['q'] = $busqueda;
    }

    return '/buscar.php?' . http_build_query($params);
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

function contarUsuariosBusqueda(PDO $db, $busqueda) {
    $textoBusqueda = '%' . trim((string) $busqueda) . '%';
    $stmt = $db->prepare('SELECT COUNT(*)
                          FROM USUARIO
                          WHERE activo = 1 AND (nombre LIKE ? OR nick LIKE ?)');
    $stmt->execute([$textoBusqueda, $textoBusqueda]);

    return (int) $stmt->fetchColumn();
}

function buscarUsuariosBusqueda(PDO $db, $busqueda, $porPagina, $offset) {
    $busqueda = trim((string) $busqueda);
    $textoBusqueda = '%' . $busqueda . '%';
    $empiezaPor = $busqueda . '%';

    $stmt = $db->prepare('SELECT u.id, u.nombre, u.nick, u.avatar,
                                 (SELECT COUNT(*) FROM USUARIO_JUEGO uj WHERE uj.id_usuario = u.id) AS total_juegos,
                                 (SELECT COUNT(*) FROM RESENA r WHERE r.id_usuario = u.id AND r.activa = 1 AND r.comentario IS NOT NULL AND TRIM(r.comentario) <> \'\') AS total_resenas
                          FROM USUARIO u
                          WHERE u.activo = 1 AND (u.nombre LIKE :texto_nombre OR u.nick LIKE :texto_nick)
                          ORDER BY
                              CASE
                                  WHEN u.nick = :exacto_nick THEN 0
                                  WHEN u.nick LIKE :empieza_nick THEN 1
                                  WHEN u.nombre LIKE :empieza_nombre THEN 2
                                  ELSE 3
                              END,
                              u.nick ASC
                          LIMIT :limite OFFSET :offset');
    $stmt->bindValue(':texto_nombre', $textoBusqueda);
    $stmt->bindValue(':texto_nick', $textoBusqueda);
    $stmt->bindValue(':exacto_nick', $busqueda);
    $stmt->bindValue(':empieza_nick', $empiezaPor);
    $stmt->bindValue(':empieza_nombre', $empiezaPor);
    $stmt->bindValue(':limite', max(1, (int) $porPagina), PDO::PARAM_INT);
    $stmt->bindValue(':offset', max(0, (int) $offset), PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function datosBusquedaUsuarios(PDO $db, $busqueda, $paginaActual = 1, $porPagina = BUSQUEDA_USUARIOS_POR_PAGINA) {
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
            'total_usuarios' => 0,
            'total_paginas' => 1,
            'usuarios' => []
        ];
    }

    $totalUsuarios = contarUsuariosBusqueda($db, $busqueda);
    $totalPaginas = max(1, (int) ceil($totalUsuarios / $porPagina));

    if ($paginaActual > $totalPaginas) {
        $paginaActual = $totalPaginas;
    }

    $offset = ($paginaActual - 1) * $porPagina;

    return [
        'busqueda' => $busqueda,
        'longitud' => $longitud,
        'pagina_actual' => $paginaActual,
        'por_pagina' => $porPagina,
        'total_usuarios' => $totalUsuarios,
        'total_paginas' => $totalPaginas,
        'usuarios' => buscarUsuariosBusqueda($db, $busqueda, $porPagina, $offset)
    ];
}

function textoResumenBusqueda($busqueda, $totalResultados, $tipo = 'juegos') {
    $busqueda = trim((string) $busqueda);
    $tipo = tipoBusquedaValido($tipo);

    if ($busqueda === '') {
        return 'Busca juegos o usuarios.';
    }

    $etiqueta = $tipo === 'usuarios' ? 'usuarios' : 'juegos';

    return number_format((int) $totalResultados, 0, ',', '.') . ' ' . $etiqueta . ' para "' . $busqueda . '"';
}

function htmlTabsBusqueda($busqueda, $tipoActivo) {
    $tipoActivo = tipoBusquedaValido($tipoActivo);

    ob_start(); ?>
    <nav class="tabs-busqueda" aria-label="Tipo de búsqueda">
        <a href="<?= htmlspecialchars(urlTabBusqueda($busqueda, 'juegos')) ?>"<?= $tipoActivo === 'juegos' ? ' class="active"' : '' ?>>Juegos</a>
        <a href="<?= htmlspecialchars(urlTabBusqueda($busqueda, 'usuarios')) ?>"<?= $tipoActivo === 'usuarios' ? ' class="active"' : '' ?>>Usuarios</a>
    </nav>
    <?php

    return trim((string) ob_get_clean());
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

function htmlResultadosUsuariosBusqueda($usuarios, $busqueda, $longitud) {
    ob_start();

    if ($usuarios):
        foreach ($usuarios as $usuario): ?>
            <a class="usuario-busqueda" href="<?= htmlspecialchars(urlUsuarioPublico($usuario['nick'])) ?>">
                <img src="<?= htmlspecialchars(urlAvatarUsuario($usuario['avatar'] ?? '')) ?>" alt="Foto de perfil de <?= htmlspecialchars($usuario['nick']) ?>">
                <span class="datos-usuario-busqueda">
                    <strong><?= htmlspecialchars($usuario['nombre']) ?></strong>
                    <span>@<?= htmlspecialchars($usuario['nick']) ?></span>
                </span>
                <span class="stats-usuario-busqueda">
                    <span><?= (int) $usuario['total_juegos'] ?> juegos</span>
                    <span><?= (int) $usuario['total_resenas'] ?> reseñas</span>
                </span>
            </a>
        <?php endforeach;
    elseif ($busqueda !== '' && $longitud >= BUSQUEDA_MINIMA_JUEGOS): ?>
        <div class="sin-resultados">
            <p>No se ha encontrado ningún usuario.</p>
        </div>
    <?php elseif ($busqueda !== ''): ?>
        <div class="sin-resultados">
            <p>Escribe al menos <?= BUSQUEDA_MINIMA_JUEGOS ?> caracteres para buscar.</p>
        </div>
    <?php else: ?>
        <div class="sin-resultados">
            <p>Escribe un nombre o nick para buscar.</p>
        </div>
    <?php endif;

    return trim((string) ob_get_clean());
}

function htmlPaginacionBusqueda($busqueda, $paginaActual, $totalPaginas, $tipo = 'juegos') {
    if ($totalPaginas <= 1) {
        return '';
    }

    ob_start(); ?>
    <nav class="paginacion">
        <?php if ($paginaActual > 1): ?>
            <a href="<?= htmlspecialchars(urlBusqueda($busqueda, $paginaActual - 1, $tipo)) ?>">Anterior</a>
        <?php endif; ?>

        <?php foreach (paginasBusqueda($paginaActual, $totalPaginas) as $item): ?>
            <?php if ($item === '...'): ?>
                <span class="separador">...</span>
            <?php else: ?>
                <a href="<?= htmlspecialchars(urlBusqueda($busqueda, $item, $tipo)) ?>"<?= $item === $paginaActual ? ' class="active"' : '' ?>><?= $item ?></a>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($paginaActual < $totalPaginas): ?>
            <a href="<?= htmlspecialchars(urlBusqueda($busqueda, $paginaActual + 1, $tipo)) ?>">Siguiente</a>
        <?php endif; ?>
    </nav>
    <?php

    return trim((string) ob_get_clean());
}
