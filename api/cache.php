<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/igdb.php';

function cacheFechaCaducada($fechaCache, $horas = 72) {
    if (!$fechaCache) {
        return true;
    }

    $marca = strtotime($fechaCache);

    if ($marca === false) {
        return true;
    }

    return $marca < strtotime('-' . (int) $horas . ' hours');
}

function cacheValorTexto($valor) {
    $valor = trim((string) $valor);

    return $valor === '' ? null : $valor;
}

function cacheValorFechaIgdb($valor) {
    $valor = (int) $valor;

    if ($valor <= 0) {
        return null;
    }

    return date('Y-m-d', $valor);
}

function cacheGuardarDesarrolladora(PDO $db, $datos) {
    $compania = $datos['company'] ?? $datos;
    $nombre = cacheValorTexto($compania['name'] ?? '');
    $igdbId = (int) ($compania['id'] ?? 0);

    if (!$nombre || $igdbId <= 0) {
        return null;
    }

    $stmt = $db->prepare('SELECT id FROM DESARROLLADORA WHERE igdb_id = ? LIMIT 1');
    $stmt->execute([$igdbId]);
    $existente = $stmt->fetchColumn();

    if ($existente) {
        $update = $db->prepare('UPDATE DESARROLLADORA SET nombre = ? WHERE id = ?');
        $update->execute([$nombre, $existente]);

        return (int) $existente;
    }

    $stmt = $db->prepare('SELECT id FROM DESARROLLADORA WHERE nombre = ? LIMIT 1');
    $stmt->execute([$nombre]);
    $porNombre = $stmt->fetchColumn();

    if ($porNombre) {
        $update = $db->prepare('UPDATE DESARROLLADORA SET igdb_id = ? WHERE id = ?');
        $update->execute([$igdbId, $porNombre]);

        return (int) $porNombre;
    }

    $insert = $db->prepare('INSERT INTO DESARROLLADORA (nombre, pais, igdb_id) VALUES (?, ?, ?)');
    $insert->execute([$nombre, null, $igdbId]);

    return (int) $db->lastInsertId();
}

function cacheGuardarGenero(PDO $db, $datos) {
    $nombre = cacheValorTexto($datos['name'] ?? '');
    $igdbId = (int) ($datos['id'] ?? 0);

    if (!$nombre || $igdbId <= 0) {
        return null;
    }

    $stmt = $db->prepare('SELECT id FROM GENERO WHERE igdb_id = ? LIMIT 1');
    $stmt->execute([$igdbId]);
    $existente = $stmt->fetchColumn();

    if ($existente) {
        $update = $db->prepare('UPDATE GENERO SET nombre = ? WHERE id = ?');
        $update->execute([$nombre, $existente]);

        return (int) $existente;
    }

    $stmt = $db->prepare('SELECT id FROM GENERO WHERE nombre = ? LIMIT 1');
    $stmt->execute([$nombre]);
    $porNombre = $stmt->fetchColumn();

    if ($porNombre) {
        $update = $db->prepare('UPDATE GENERO SET igdb_id = ? WHERE id = ?');
        $update->execute([$igdbId, $porNombre]);

        return (int) $porNombre;
    }

    $insert = $db->prepare('INSERT INTO GENERO (nombre, igdb_id) VALUES (?, ?)');
    $insert->execute([$nombre, $igdbId]);

    return (int) $db->lastInsertId();
}

function cacheGuardarPlataforma(PDO $db, $datos) {
    $nombre = cacheValorTexto($datos['name'] ?? '');
    $igdbId = (int) ($datos['id'] ?? 0);

    if (!$nombre || $igdbId <= 0) {
        return null;
    }

    $acronimo = strtoupper(substr($nombre, 0, 5));

    $stmt = $db->prepare('SELECT id FROM PLATAFORMA WHERE igdb_id = ? LIMIT 1');
    $stmt->execute([$igdbId]);
    $existente = $stmt->fetchColumn();

    if ($existente) {
        $update = $db->prepare('UPDATE PLATAFORMA SET nombre = ?, acronimo = ? WHERE id = ?');
        $update->execute([$nombre, $acronimo, $existente]);

        return (int) $existente;
    }

    $stmt = $db->prepare('SELECT id FROM PLATAFORMA WHERE nombre = ? LIMIT 1');
    $stmt->execute([$nombre]);
    $porNombre = $stmt->fetchColumn();

    if ($porNombre) {
        $update = $db->prepare('UPDATE PLATAFORMA SET igdb_id = ?, acronimo = ? WHERE id = ?');
        $update->execute([$igdbId, $acronimo, $porNombre]);

        return (int) $porNombre;
    }

    $insert = $db->prepare('INSERT INTO PLATAFORMA (nombre, acronimo, igdb_id) VALUES (?, ?, ?)');
    $insert->execute([$nombre, $acronimo, $igdbId]);

    return (int) $db->lastInsertId();
}

function cacheSyncGeneros(PDO $db, $idVideojuego, $generos) {
    $delete = $db->prepare('DELETE FROM VIDEOJUEGO_GENERO WHERE id_videojuego = ?');
    $delete->execute([$idVideojuego]);

    if (!is_array($generos)) {
        return;
    }

    $insert = $db->prepare('INSERT IGNORE INTO VIDEOJUEGO_GENERO (id_videojuego, id_genero) VALUES (?, ?)');

    foreach ($generos as $genero) {
        $idGenero = cacheGuardarGenero($db, $genero);

        if ($idGenero) {
            $insert->execute([$idVideojuego, $idGenero]);
        }
    }
}

function cacheSyncPlataformas(PDO $db, $idVideojuego, $plataformas) {
    $delete = $db->prepare('DELETE FROM VIDEOJUEGO_PLATAFORMA WHERE id_videojuego = ?');
    $delete->execute([$idVideojuego]);

    if (!is_array($plataformas)) {
        return;
    }

    $insert = $db->prepare('INSERT IGNORE INTO VIDEOJUEGO_PLATAFORMA (id_videojuego, id_plataforma) VALUES (?, ?)');

    foreach ($plataformas as $plataforma) {
        $idPlataforma = cacheGuardarPlataforma($db, $plataforma);

        if ($idPlataforma) {
            $insert->execute([$idVideojuego, $idPlataforma]);
        }
    }
}

function cacheDesarrolladoraJuego($juego) {
    $companias = $juego['involved_companies'] ?? [];

    if (!is_array($companias)) {
        return null;
    }

    foreach ($companias as $compania) {
        if (!empty($compania['developer']) && !empty($compania['company'])) {
            return $compania['company'];
        }
    }

    if (!empty($companias[0]['company'])) {
        return $companias[0]['company'];
    }

    return null;
}

function cacheGuardarJuegoIgdb(PDO $db, $juego) {
    $igdbId = (int) ($juego['id'] ?? 0);
    $titulo = cacheValorTexto($juego['name'] ?? '');

    if ($igdbId <= 0 || !$titulo) {
        return null;
    }

    $desarrolladora = cacheDesarrolladoraJuego($juego);
    $idDesarrolladora = $desarrolladora ? cacheGuardarDesarrolladora($db, $desarrolladora) : null;

    $stmt = $db->prepare('SELECT id, descripcion FROM VIDEOJUEGO WHERE igdb_id = ? LIMIT 1');
    $stmt->execute([$igdbId]);
    $existente = $stmt->fetch();

    $descripcion = cacheValorTexto($juego['summary'] ?? '');

    if ($existente && !$descripcion) {
        $descripcion = $existente['descripcion'];
    }

    $datos = [
        $titulo,
        igdbPortadaJuego($juego['cover'] ?? null),
        igdbBackgroundJuego($juego),
        cacheValorFechaIgdb($juego['first_release_date'] ?? 0),
        $descripcion,
        $idDesarrolladora
    ];

    if ($existente) {
        $update = $db->prepare('UPDATE VIDEOJUEGO SET titulo = ?, portada_url = ?, background_url = ?, fecha_lanzamiento = ?, descripcion = ?, id_desarrolladora = ?, fecha_cache = NOW() WHERE id = ?');
        $update->execute(array_merge($datos, [$existente['id']]));
        $idVideojuego = (int) $existente['id'];
    } else {
        $insert = $db->prepare('INSERT INTO VIDEOJUEGO (igdb_id, titulo, portada_url, background_url, fecha_lanzamiento, descripcion, id_desarrolladora, fecha_cache) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
        $insert->execute(array_merge([$igdbId], $datos));
        $idVideojuego = (int) $db->lastInsertId();
    }

    cacheSyncGeneros($db, $idVideojuego, $juego['genres'] ?? []);
    cacheSyncPlataformas($db, $idVideojuego, $juego['platforms'] ?? []);

    return $idVideojuego;
}

function cacheObtenerJuegoPorIgdbId(PDO $db, $igdbId) {
    $stmt = $db->prepare('SELECT * FROM VIDEOJUEGO WHERE igdb_id = ? LIMIT 1');
    $stmt->execute([(int) $igdbId]);

    return $stmt->fetch() ?: null;
}

function cacheActualizarJuegoPorIgdbId(PDO $db, $igdbId) {
    $detalle = igdbObtenerJuego($igdbId);

    if (!$detalle || empty($detalle['id'])) {
        return null;
    }

    cacheGuardarJuegoIgdb($db, $detalle);

    return cacheObtenerJuegoPorIgdbId($db, $igdbId);
}

function cacheObtenerJuegoIgdb(PDO $db, $igdbId, $horas = 72) {
    $juego = cacheObtenerJuegoPorIgdbId($db, $igdbId);

    $backgroundAntiguo = $juego && !empty($juego['background_url']) && str_contains($juego['background_url'], '/t_thumb/');

    if ($juego && !$backgroundAntiguo && !cacheFechaCaducada($juego['fecha_cache'], $horas)) {
        return $juego;
    }

    if (!igdbDisponible()) {
        return $juego;
    }

    $actualizado = cacheActualizarJuegoPorIgdbId($db, $igdbId);

    return $actualizado ?: $juego;
}

function cacheGenerosJuego(PDO $db, $idVideojuego) {
    $stmt = $db->prepare('SELECT g.nombre
                          FROM VIDEOJUEGO_GENERO vg
                          INNER JOIN GENERO g ON g.id = vg.id_genero
                          WHERE vg.id_videojuego = ?
                          ORDER BY g.nombre ASC');
    $stmt->execute([(int) $idVideojuego]);

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function cachePlataformasJuego(PDO $db, $idVideojuego) {
    $stmt = $db->prepare('SELECT p.nombre
                          FROM VIDEOJUEGO_PLATAFORMA vp
                          INNER JOIN PLATAFORMA p ON p.id = vp.id_plataforma
                          WHERE vp.id_videojuego = ?
                          ORDER BY p.nombre ASC');
    $stmt->execute([(int) $idVideojuego]);

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function cacheResumenResenasJuego(PDO $db, $idVideojuego) {
    $stmt = $db->prepare('SELECT COUNT(*) AS total, AVG(puntuacion) AS media
                          FROM RESENA
                          WHERE id_videojuego = ? AND activa = 1');
    $stmt->execute([(int) $idVideojuego]);
    $resumen = $stmt->fetch();

    if (!$resumen) {
        return [
            'total' => 0,
            'media' => null
        ];
    }

    return [
        'total' => (int) ($resumen['total'] ?? 0),
        'media' => $resumen['media'] !== null ? round(((float) $resumen['media']) / 20, 1) : null
    ];
}

function cacheHistogramaJuego(PDO $db, $idVideojuego) {
    $stmt = $db->prepare('SELECT
                            SUM(CASE WHEN puntuacion <= 20 THEN 1 ELSE 0 END) AS estrella_1,
                            SUM(CASE WHEN puntuacion > 20 AND puntuacion <= 40 THEN 1 ELSE 0 END) AS estrella_2,
                            SUM(CASE WHEN puntuacion > 40 AND puntuacion <= 60 THEN 1 ELSE 0 END) AS estrella_3,
                            SUM(CASE WHEN puntuacion > 60 AND puntuacion <= 80 THEN 1 ELSE 0 END) AS estrella_4,
                            SUM(CASE WHEN puntuacion > 80 THEN 1 ELSE 0 END) AS estrella_5
                          FROM RESENA
                          WHERE id_videojuego = ? AND activa = 1');
    $stmt->execute([(int) $idVideojuego]);
    $datos = $stmt->fetch() ?: [];

    return [
        1 => (int) ($datos['estrella_1'] ?? 0),
        2 => (int) ($datos['estrella_2'] ?? 0),
        3 => (int) ($datos['estrella_3'] ?? 0),
        4 => (int) ($datos['estrella_4'] ?? 0),
        5 => (int) ($datos['estrella_5'] ?? 0)
    ];
}

function cacheUsuarioJuego(PDO $db, $idVideojuego, $idUsuario) {
    $stmt = $db->prepare('SELECT uj.estado, uj.favorito, p.nombre AS plataforma, r.puntuacion AS puntuacion_usuario
                          FROM USUARIO_JUEGO uj
                          LEFT JOIN PLATAFORMA p ON p.id = uj.id_plataforma
                          LEFT JOIN RESENA r ON r.id_usuario = uj.id_usuario AND r.id_videojuego = uj.id_videojuego AND r.activa = 1
                          WHERE uj.id_videojuego = ? AND uj.id_usuario = ?
                          LIMIT 1');
    $stmt->execute([(int) $idVideojuego, (int) $idUsuario]);
    $datos = $stmt->fetch();

    if (!$datos) {
        return null;
    }

    if ($datos['puntuacion_usuario'] !== null) {
        $datos['puntuacion_usuario'] = round(((float) $datos['puntuacion_usuario']) / 20, 1);
    }

    $datos['favorito'] = !empty($datos['favorito']);

    return $datos;
}

function cacheResenasJuego(PDO $db, $idVideojuego, $limite = 6) {
    $stmt = $db->prepare('SELECT r.comentario, r.puntuacion, r.fecha_publicacion, u.nick, u.nombre, u.avatar, p.nombre AS plataforma
                          FROM RESENA r
                          INNER JOIN USUARIO u ON u.id = r.id_usuario
                          LEFT JOIN USUARIO_JUEGO uj ON uj.id_usuario = r.id_usuario AND uj.id_videojuego = r.id_videojuego
                          LEFT JOIN PLATAFORMA p ON p.id = uj.id_plataforma
                          WHERE r.id_videojuego = ? AND r.activa = 1
                          ORDER BY r.fecha_publicacion DESC
                          LIMIT ' . (int) $limite);
    $stmt->execute([(int) $idVideojuego]);
    $resenas = $stmt->fetchAll();

    foreach ($resenas as &$resena) {
        $resena['puntuacion_estrellas'] = $resena['puntuacion'] !== null ? round(((float) $resena['puntuacion']) / 20, 1) : null;
    }

    return $resenas;
}

function cacheDetalleJuego(PDO $db, $igdbId, $idUsuario = 0, $horas = 72) {
    $juegoBase = cacheObtenerJuegoIgdb($db, $igdbId, $horas);

    if (!$juegoBase) {
        return null;
    }

    $stmt = $db->prepare('SELECT v.*, d.nombre AS desarrolladora
                          FROM VIDEOJUEGO v
                          LEFT JOIN DESARROLLADORA d ON d.id = v.id_desarrolladora
                          WHERE v.id = ?
                          LIMIT 1');
    $stmt->execute([(int) $juegoBase['id']]);
    $juego = $stmt->fetch();

    if (!$juego) {
        return null;
    }

    $juego['generos'] = cacheGenerosJuego($db, $juego['id']);
    $juego['plataformas'] = cachePlataformasJuego($db, $juego['id']);
    $juego['resumen_resenas'] = cacheResumenResenasJuego($db, $juego['id']);
    $juego['histograma'] = cacheHistogramaJuego($db, $juego['id']);
    $juego['resenas'] = cacheResenasJuego($db, $juego['id']);
    $juego['usuario_juego'] = $idUsuario > 0 ? cacheUsuarioJuego($db, $juego['id'], $idUsuario) : null;

    return $juego;
}

function cacheVaciarCatalogo(PDO $db) {
    $db->exec('DELETE FROM REPORTE');
    $db->exec('DELETE FROM RESENA');
    $db->exec('DELETE FROM USUARIO_JUEGO');
    $db->exec('DELETE FROM LISTA_VIDEOJUEGO');
    $db->exec('DELETE FROM VIDEOJUEGO_GENERO');
    $db->exec('DELETE FROM VIDEOJUEGO_PLATAFORMA');
    $db->exec('DELETE FROM VIDEOJUEGO');
    $db->exec('DELETE FROM GENERO');
    $db->exec('DELETE FROM PLATAFORMA');
    $db->exec('DELETE FROM DESARROLLADORA');
    $db->exec('ALTER TABLE VIDEOJUEGO AUTO_INCREMENT = 1');
    $db->exec('ALTER TABLE GENERO AUTO_INCREMENT = 1');
    $db->exec('ALTER TABLE PLATAFORMA AUTO_INCREMENT = 1');
    $db->exec('ALTER TABLE DESARROLLADORA AUTO_INCREMENT = 1');
}

function cacheImportarJuegosIgdb(PDO $db, $pagina = 1, $cantidad = 20, $reiniciar = false) {
    if (!igdbDisponible()) {
        return [
            'ok' => false,
            'mensaje' => 'No hay credenciales de IGDB configuradas',
            'importados' => 0
        ];
    }

    if ($reiniciar) {
        cacheVaciarCatalogo($db);
    }

    $respuesta = igdbPopulares($pagina, $cantidad);

    if (!$respuesta) {
        return [
            'ok' => false,
            'mensaje' => 'No se han podido obtener juegos desde IGDB',
            'importados' => 0
        ];
    }

    $importados = 0;

    foreach ($respuesta as $juego) {
        if (cacheGuardarJuegoIgdb($db, $juego)) {
            $importados++;
        }
    }

    return [
        'ok' => true,
        'mensaje' => 'Importacion completada',
        'importados' => $importados
    ];
}

function cacheConstruirFiltrosCatalogo($filtros) {
    $where = [];
    $params = [];
    $joins = '';

    if (!empty($filtros['genero'])) {
        $joins .= ' INNER JOIN VIDEOJUEGO_GENERO vg ON vg.id_videojuego = v.id ';
        $where[] = 'vg.id_genero = ?';
        $params[] = (int) $filtros['genero'];
    }

    if (!empty($filtros['plataforma'])) {
        $joins .= ' INNER JOIN VIDEOJUEGO_PLATAFORMA vp ON vp.id_videojuego = v.id ';
        $where[] = 'vp.id_plataforma = ?';
        $params[] = (int) $filtros['plataforma'];
    }

    if (!empty($filtros['anio'])) {
        $where[] = 'YEAR(v.fecha_lanzamiento) = ?';
        $params[] = (int) $filtros['anio'];
    }

    return [
        'joins' => $joins,
        'where' => $where,
        'params' => $params
    ];
}

function cacheOrdenCatalogo($orden) {
    $opciones = [
        'puntuacion' => 'COALESCE(r.puntuacion_media, -1) DESC, v.titulo ASC',
        'nombre' => 'v.titulo ASC',
        'fecha' => 'v.fecha_lanzamiento DESC, v.titulo ASC'
    ];

    return $opciones[$orden] ?? $opciones['puntuacion'];
}

function cacheContarJuegosCatalogo(PDO $db, $filtros = []) {
    $partes = cacheConstruirFiltrosCatalogo($filtros);
    $sql = 'SELECT COUNT(DISTINCT v.id) FROM VIDEOJUEGO v ' . $partes['joins'];

    if (!empty($partes['where'])) {
        $sql .= ' WHERE ' . implode(' AND ', $partes['where']);
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($partes['params']);

    return (int) $stmt->fetchColumn();
}

function cacheListarJuegosCatalogo(PDO $db, $filtros = [], $orden = 'puntuacion', $limite = 12, $offset = 0) {
    $partes = cacheConstruirFiltrosCatalogo($filtros);
    $sql = 'SELECT DISTINCT v.id, v.igdb_id, v.titulo, v.portada_url, v.fecha_lanzamiento, r.puntuacion_media
            FROM VIDEOJUEGO v
            LEFT JOIN (
                SELECT id_videojuego, ROUND(AVG(puntuacion), 1) AS puntuacion_media
                FROM RESENA
                WHERE activa = 1
                GROUP BY id_videojuego
            ) r ON r.id_videojuego = v.id ' . $partes['joins'];

    if (!empty($partes['where'])) {
        $sql .= ' WHERE ' . implode(' AND ', $partes['where']);
    }

    $sql .= ' ORDER BY ' . cacheOrdenCatalogo($orden) . ' LIMIT ' . (int) $limite . ' OFFSET ' . (int) $offset;

    $stmt = $db->prepare($sql);
    $stmt->execute($partes['params']);

    return $stmt->fetchAll();
}

function cacheOpcionesGeneros(PDO $db) {
    $stmt = $db->query('SELECT id, nombre FROM GENERO ORDER BY nombre ASC');

    return $stmt->fetchAll();
}

function cacheOpcionesPlataformas(PDO $db) {
    $stmt = $db->query('SELECT id, nombre FROM PLATAFORMA ORDER BY nombre ASC');

    return $stmt->fetchAll();
}

function cacheOpcionesAnos(PDO $db) {
    $stmt = $db->query('SELECT DISTINCT YEAR(fecha_lanzamiento) AS anio FROM VIDEOJUEGO WHERE fecha_lanzamiento IS NOT NULL ORDER BY anio DESC');

    return $stmt->fetchAll();
}
