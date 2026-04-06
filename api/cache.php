<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/rawg.php';

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

function cacheValorFecha($valor) {
    $valor = trim((string) $valor);

    if ($valor === '') {
        return null;
    }

    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) ? $valor : null;
}

function cacheGuardarDesarrolladora(PDO $db, $datos) {
    $nombre = cacheValorTexto($datos['name'] ?? '');
    $rawgId = (int) ($datos['id'] ?? 0);

    if (!$nombre || $rawgId <= 0) {
        return null;
    }

    $stmt = $db->prepare('SELECT id FROM DESARROLLADORA WHERE rawg_id = ? LIMIT 1');
    $stmt->execute([$rawgId]);
    $existente = $stmt->fetchColumn();

    if ($existente) {
        $update = $db->prepare('UPDATE DESARROLLADORA SET nombre = ?, pais = ? WHERE id = ?');
        $update->execute([
            $nombre,
            cacheValorTexto($datos['country'] ?? ''),
            $existente
        ]);

        return (int) $existente;
    }

    $insert = $db->prepare('INSERT INTO DESARROLLADORA (nombre, pais, rawg_id) VALUES (?, ?, ?)');
    $insert->execute([
        $nombre,
        cacheValorTexto($datos['country'] ?? ''),
        $rawgId
    ]);

    return (int) $db->lastInsertId();
}

function cacheGuardarGenero(PDO $db, $datos) {
    $nombre = cacheValorTexto($datos['name'] ?? '');
    $rawgId = (int) ($datos['id'] ?? 0);

    if (!$nombre || $rawgId <= 0) {
        return null;
    }

    $stmt = $db->prepare('SELECT id FROM GENERO WHERE rawg_id = ? LIMIT 1');
    $stmt->execute([$rawgId]);
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
        $update = $db->prepare('UPDATE GENERO SET rawg_id = ? WHERE id = ?');
        $update->execute([$rawgId, $porNombre]);

        return (int) $porNombre;
    }

    $insert = $db->prepare('INSERT INTO GENERO (nombre, rawg_id) VALUES (?, ?)');
    $insert->execute([$nombre, $rawgId]);

    return (int) $db->lastInsertId();
}

function cacheGuardarPlataforma(PDO $db, $datos) {
    $plataforma = $datos['platform'] ?? $datos;
    $nombre = cacheValorTexto($plataforma['name'] ?? '');
    $rawgId = (int) ($plataforma['id'] ?? 0);

    if (!$nombre || $rawgId <= 0) {
        return null;
    }

    $acronimo = strtoupper(substr($nombre, 0, 5));

    $stmt = $db->prepare('SELECT id FROM PLATAFORMA WHERE rawg_id = ? LIMIT 1');
    $stmt->execute([$rawgId]);
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
        $update = $db->prepare('UPDATE PLATAFORMA SET rawg_id = ?, acronimo = ? WHERE id = ?');
        $update->execute([$rawgId, $acronimo, $porNombre]);

        return (int) $porNombre;
    }

    $insert = $db->prepare('INSERT INTO PLATAFORMA (nombre, acronimo, rawg_id) VALUES (?, ?, ?)');
    $insert->execute([$nombre, $acronimo, $rawgId]);

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

function cacheGuardarJuegoRawg(PDO $db, $juego) {
    $rawgId = (int) ($juego['id'] ?? 0);
    $titulo = cacheValorTexto($juego['name'] ?? '');

    if ($rawgId <= 0 || !$titulo) {
        return null;
    }

    $desarrolladoras = $juego['developers'] ?? [];
    $idDesarrolladora = null;

    if (is_array($desarrolladoras) && !empty($desarrolladoras)) {
        $idDesarrolladora = cacheGuardarDesarrolladora($db, $desarrolladoras[0]);
    }

    $stmt = $db->prepare('SELECT id, descripcion FROM VIDEOJUEGO WHERE rawg_id = ? LIMIT 1');
    $stmt->execute([$rawgId]);
    $existente = $stmt->fetch();

    $descripcion = cacheValorTexto($juego['description_raw'] ?? ($juego['description'] ?? ''));

    if ($existente && !$descripcion) {
        $descripcion = $existente['descripcion'];
    }

    $datos = [
        $titulo,
        cacheValorTexto($juego['background_image'] ?? ''),
        cacheValorTexto($juego['background_image_additional'] ?? ($juego['background_image'] ?? '')),
        cacheValorFecha($juego['released'] ?? ''),
        $descripcion,
        isset($juego['rating']) ? (float) $juego['rating'] : null,
        $idDesarrolladora
    ];

    if ($existente) {
        $update = $db->prepare('UPDATE VIDEOJUEGO SET titulo = ?, portada_url = ?, background_url = ?, fecha_lanzamiento = ?, descripcion = ?, rating_rawg = ?, id_desarrolladora = ?, fecha_cache = NOW() WHERE id = ?');
        $update->execute(array_merge($datos, [$existente['id']]));
        $idVideojuego = (int) $existente['id'];
    } else {
        $insert = $db->prepare('INSERT INTO VIDEOJUEGO (rawg_id, titulo, portada_url, background_url, fecha_lanzamiento, descripcion, rating_rawg, id_desarrolladora, fecha_cache) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $insert->execute(array_merge([$rawgId], $datos));
        $idVideojuego = (int) $db->lastInsertId();
    }

    cacheSyncGeneros($db, $idVideojuego, $juego['genres'] ?? []);
    cacheSyncPlataformas($db, $idVideojuego, $juego['platforms'] ?? []);

    return $idVideojuego;
}

function cacheObtenerJuegoPorRawgId(PDO $db, $rawgId) {
    $stmt = $db->prepare('SELECT * FROM VIDEOJUEGO WHERE rawg_id = ? LIMIT 1');
    $stmt->execute([(int) $rawgId]);

    return $stmt->fetch() ?: null;
}

function cacheActualizarJuegoPorRawgId(PDO $db, $rawgId) {
    $detalle = rawgObtenerJuego($rawgId);

    if (!$detalle || empty($detalle['id'])) {
        return null;
    }

    cacheGuardarJuegoRawg($db, $detalle);

    return cacheObtenerJuegoPorRawgId($db, $rawgId);
}

function cacheObtenerJuegoRawg(PDO $db, $rawgId, $horas = 72) {
    $juego = cacheObtenerJuegoPorRawgId($db, $rawgId);

    if ($juego && !cacheFechaCaducada($juego['fecha_cache'], $horas)) {
        return $juego;
    }

    if (!rawgDisponible()) {
        return $juego;
    }

    $actualizado = cacheActualizarJuegoPorRawgId($db, $rawgId);

    return $actualizado ?: $juego;
}

function cacheImportarPopulares(PDO $db, $pagina = 1, $cantidad = 20) {
    if (!rawgDisponible()) {
        return [
            'ok' => false,
            'mensaje' => 'No hay clave de RAWG configurada',
            'importados' => 0
        ];
    }

    $respuesta = rawgPopulares($pagina, $cantidad);

    if (!$respuesta || empty($respuesta['results'])) {
        return [
            'ok' => false,
            'mensaje' => 'No se han podido obtener juegos desde RAWG',
            'importados' => 0
        ];
    }

    $importados = 0;

    foreach ($respuesta['results'] as $juego) {
        $detalle = rawgObtenerJuego($juego['id'] ?? 0);
        $datos = $detalle ?: $juego;

        if (cacheGuardarJuegoRawg($db, $datos)) {
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
        'puntuacion' => 'v.rating_rawg DESC, v.titulo ASC',
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
    $sql = 'SELECT DISTINCT v.id, v.rawg_id, v.titulo, v.portada_url, v.rating_rawg, v.fecha_lanzamiento
            FROM VIDEOJUEGO v ' . $partes['joins'];

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
