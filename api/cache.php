<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/igdb.php';

const LIMITE_FAVORITOS_USUARIO = 10;
const HORAS_CACHE_JUEGO_IGDB = 720;

function cachePlataformaPermitida($nombre) {
    $nombre = cacheTextoNormalizado($nombre);

    if ($nombre === '') {
        return false;
    }

    if (in_array($nombre, ['linux', 'android', 'ios', 'iphone', 'ipad', 'windows pc', 'pc (microsoft windows)'], true)) {
        return true;
    }

    return str_contains($nombre, 'playstation')
        || str_contains($nombre, 'xbox')
        || str_contains($nombre, 'nintendo');
}

function cacheJuegoTienePlataformaPermitida($plataformas) {
    if (!is_array($plataformas)) {
        return false;
    }

    foreach ($plataformas as $plataforma) {
        if (cachePlataformaPermitida($plataforma['name'] ?? $plataforma['nombre'] ?? '')) {
            return true;
        }
    }

    return false;
}

function cachePuntuacionUsuarioEstrellas($puntuacion) {
    if ($puntuacion === null) {
        return null;
    }

    return round((((float) $puntuacion) / 20) * 2) / 2;
}

function cachePuntuacionMediaEstrellas($puntuacion) {
    if ($puntuacion === null) {
        return null;
    }

    return round(((float) $puntuacion) / 20, 1);
}

function cachePuntuacionResenaValida($puntuacion) {
    if ($puntuacion === null || $puntuacion === '') {
        return false;
    }

    $puntuacion = (int) $puntuacion;

    return $puntuacion >= 10 && $puntuacion <= 100 && $puntuacion % 10 === 0;
}

function cacheComentarioResenaValido($comentario, $minimo = 20, $maximo = 2000) {
    $comentario = trim((string) $comentario);
    $longitud = mb_strlen($comentario, 'UTF-8');

    return $longitud >= (int) $minimo && $longitud <= (int) $maximo;
}

function cacheComentarioResenaPublicado($comentario) {
    return trim((string) $comentario) !== '';
}

function cacheFechaCaducada($fechaCache, $horas = HORAS_CACHE_JUEGO_IGDB) {
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

function cacheTextoNormalizado($texto) {
    return mb_strtolower(trim((string) $texto), 'UTF-8');
}

function cacheValorFechaIgdb($valor) {
    $valor = (int) $valor;

    if ($valor <= 0) {
        return null;
    }

    return date('Y-m-d', $valor);
}

function cachePuntuacionIgdbVisible($puntuacion) {
    if ($puntuacion === null || $puntuacion === '') {
        return null;
    }

    return round(((float) $puntuacion) / 20, 1);
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

    $insert = $db->prepare('INSERT INTO DESARROLLADORA (nombre, igdb_id) VALUES (?, ?)');
    $insert->execute([$nombre, $igdbId]);

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

    $stmt = $db->prepare('SELECT id FROM PLATAFORMA WHERE igdb_id = ? LIMIT 1');
    $stmt->execute([$igdbId]);
    $existente = $stmt->fetchColumn();

    if ($existente) {
        $update = $db->prepare('UPDATE PLATAFORMA SET nombre = ? WHERE id = ?');
        $update->execute([$nombre, $existente]);

        return (int) $existente;
    }

    $stmt = $db->prepare('SELECT id FROM PLATAFORMA WHERE nombre = ? LIMIT 1');
    $stmt->execute([$nombre]);
    $porNombre = $stmt->fetchColumn();

    if ($porNombre) {
        $update = $db->prepare('UPDATE PLATAFORMA SET igdb_id = ? WHERE id = ?');
        $update->execute([$igdbId, $porNombre]);

        return (int) $porNombre;
    }

    $insert = $db->prepare('INSERT INTO PLATAFORMA (nombre, igdb_id) VALUES (?, ?)');
    $insert->execute([$nombre, $igdbId]);

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
    $plataformas = $juego['platforms'] ?? [];

    if ($igdbId <= 0 || !$titulo || !cacheJuegoTienePlataformaPermitida($plataformas)) {
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
        cachePuntuacionIgdbVisible($juego['total_rating'] ?? null),
        $descripcion,
        $idDesarrolladora
    ];

    if ($existente) {
        $update = $db->prepare('UPDATE VIDEOJUEGO SET titulo = ?, portada_url = ?, background_url = ?, fecha_lanzamiento = ?, puntuacion_igdb = ?, descripcion = ?, id_desarrolladora = ?, fecha_cache = NOW() WHERE id = ?');
        $update->execute(array_merge($datos, [$existente['id']]));
        $idVideojuego = (int) $existente['id'];
    } else {
        $insert = $db->prepare('INSERT INTO VIDEOJUEGO (igdb_id, titulo, portada_url, background_url, fecha_lanzamiento, puntuacion_igdb, descripcion, id_desarrolladora, fecha_cache) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $insert->execute(array_merge([$igdbId], $datos));
        $idVideojuego = (int) $db->lastInsertId();
    }

    cacheSyncGeneros($db, $idVideojuego, $juego['genres'] ?? []);
    cacheSyncPlataformas($db, $idVideojuego, $plataformas);

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

function cacheObtenerJuegoIgdb(PDO $db, $igdbId, $horas = HORAS_CACHE_JUEGO_IGDB) {
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

function cachePlataformasJuegoDetalle(PDO $db, $idVideojuego) {
    $stmt = $db->prepare('SELECT p.id, p.nombre
                          FROM VIDEOJUEGO_PLATAFORMA vp
                          INNER JOIN PLATAFORMA p ON p.id = vp.id_plataforma
                          WHERE vp.id_videojuego = ?
                          ORDER BY p.nombre ASC');
    $stmt->execute([(int) $idVideojuego]);

    $plataformas = $stmt->fetchAll();

    return cacheCompletarPlataformasJuegoDetalle($db, $plataformas);
}

function cachePuedeMarcarFavorito(PDO $db, $idUsuario, $idVideojuego = 0) {
    if ($idVideojuego > 0) {
        $stmt = $db->prepare('SELECT favorito
                              FROM USUARIO_JUEGO
                              WHERE id_usuario = ? AND id_videojuego = ?
                              LIMIT 1');
        $stmt->execute([(int) $idUsuario, (int) $idVideojuego]);
        $actual = $stmt->fetchColumn();

        if ($actual !== false && (int) $actual === 1) {
            return true;
        }
    }

    $stmt = $db->prepare('SELECT COUNT(*)
                          FROM USUARIO_JUEGO
                          WHERE id_usuario = ? AND favorito = 1');
    $stmt->execute([(int) $idUsuario]);

    return (int) $stmt->fetchColumn() < LIMITE_FAVORITOS_USUARIO;
}

function cacheCompletarPlataformasJuegoDetalle(PDO $db, $plataformas) {
    $plataformas = is_array($plataformas) ? $plataformas : [];
    $nombresNormalizados = array_map(static fn($plataforma) => cacheTextoNormalizado($plataforma['nombre'] ?? ''), $plataformas);
    $tieneWindows = in_array('windows pc', $nombresNormalizados, true) || in_array('pc (microsoft windows)', $nombresNormalizados, true);

    if ($tieneWindows && !in_array('linux', $nombresNormalizados, true)) {
        $stmt = $db->prepare('SELECT id
                              FROM PLATAFORMA
                              WHERE LOWER(nombre) = LOWER(?)
                              LIMIT 1');
        $stmt->execute(['Linux']);
        $idLinux = $stmt->fetchColumn();

        if (!$idLinux) {
            $insert = $db->prepare('INSERT INTO PLATAFORMA (nombre, igdb_id)
                                    VALUES (?, NULL)');
            $insert->execute(['Linux']);
            $idLinux = $db->lastInsertId();
        }

        $plataformas[] = [
            'id' => (int) $idLinux,
            'nombre' => 'Linux'
        ];

        usort($plataformas, static fn($a, $b) => strcasecmp((string) ($a['nombre'] ?? ''), (string) ($b['nombre'] ?? '')));
    }

    return $plataformas;
}

function cacheResumenResenasJuego(PDO $db, $idVideojuego) {
    $stmt = $db->prepare('SELECT COUNT(puntuacion) AS total, AVG(puntuacion) AS media
                          FROM RESENA
                          WHERE id_videojuego = ? AND activa = 1 AND puntuacion IS NOT NULL');
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
        'media' => cachePuntuacionMediaEstrellas($resumen['media'])
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
                          WHERE id_videojuego = ? AND activa = 1 AND puntuacion IS NOT NULL');
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
    $stmt = $db->prepare('SELECT uj.id, uj.id_plataforma, uj.estado, uj.horas_jugadas, uj.minutos_jugados, uj.fecha_inicio, uj.fecha_fin, uj.favorito, p.nombre AS plataforma, r.id AS id_resena, r.puntuacion AS puntuacion_usuario, r.comentario AS comentario_resena
                          FROM USUARIO_JUEGO uj
                          LEFT JOIN PLATAFORMA p ON p.id = uj.id_plataforma
                          LEFT JOIN RESENA r ON r.id = (
                              SELECT r2.id
                              FROM RESENA r2
                              WHERE r2.id_usuario = uj.id_usuario
                                AND r2.id_videojuego = uj.id_videojuego
                                AND r2.activa = 1
                              ORDER BY r2.fecha_publicacion DESC, r2.id DESC
                              LIMIT 1
                          )
                          WHERE uj.id_videojuego = ? AND uj.id_usuario = ?
                          LIMIT 1');
    $stmt->execute([(int) $idVideojuego, (int) $idUsuario]);
    $datos = $stmt->fetch();

    if (!$datos) {
        return null;
    }

    $datos['puntuacion_usuario'] = cachePuntuacionUsuarioEstrellas($datos['puntuacion_usuario']);
    $datos['favorito'] = !empty($datos['favorito']);
    $datos['tiene_resena_texto'] = cacheComentarioResenaPublicado($datos['comentario_resena'] ?? '');

    return $datos;
}

function cacheResenaUsuario(PDO $db, $idUsuario, $idVideojuego) {
    $stmt = $db->prepare('SELECT id, puntuacion, comentario, fecha_publicacion, activa
                          FROM RESENA
                          WHERE id_usuario = ? AND id_videojuego = ? AND activa = 1
                          ORDER BY fecha_publicacion DESC, id DESC
                          LIMIT 1');
    $stmt->execute([(int) $idUsuario, (int) $idVideojuego]);
    $resena = $stmt->fetch();

    if (!$resena) {
        return null;
    }

    $resena['tiene_comentario'] = cacheComentarioResenaPublicado($resena['comentario'] ?? '');

    return $resena;
}

function cacheContarResenasJuego(PDO $db, $idVideojuego) {
    $stmt = $db->prepare('SELECT COUNT(*)
                          FROM RESENA
                          WHERE id_videojuego = ? AND activa = 1 AND TRIM(COALESCE(comentario, "")) <> ""');
    $stmt->execute([(int) $idVideojuego]);

    return (int) $stmt->fetchColumn();
}

function cacheResenasJuego(PDO $db, $idVideojuego, $limite = 6, $offset = 0) {
    $limite = max(1, (int) $limite);
    $offset = max(0, (int) $offset);

    $stmt = $db->prepare('SELECT r.id, r.id_usuario, r.comentario, r.puntuacion, r.fecha_publicacion, u.nick, u.nombre, u.avatar, p.nombre AS plataforma
                          FROM RESENA r
                          INNER JOIN USUARIO u ON u.id = r.id_usuario
                          LEFT JOIN USUARIO_JUEGO uj ON uj.id_usuario = r.id_usuario AND uj.id_videojuego = r.id_videojuego
                          LEFT JOIN PLATAFORMA p ON p.id = uj.id_plataforma
                          WHERE r.id_videojuego = ? AND r.activa = 1 AND TRIM(COALESCE(r.comentario, "")) <> ""
                          ORDER BY r.fecha_publicacion DESC
                          LIMIT ' . $limite . ' OFFSET ' . $offset);
    $stmt->execute([(int) $idVideojuego]);
    $resenas = $stmt->fetchAll();

    foreach ($resenas as &$resena) {
        $resena['puntuacion_estrellas'] = cachePuntuacionUsuarioEstrellas($resena['puntuacion']);
    }

    return $resenas;
}

function cacheResenasRecientesInicio(PDO $db, $limite = 4) {
    $stmt = $db->prepare('SELECT
                            r.id,
                            r.id_usuario,
                            r.comentario,
                            r.puntuacion,
                            r.fecha_publicacion,
                            u.nick,
                            u.nombre,
                            u.avatar,
                            v.igdb_id,
                            v.titulo,
                            v.portada_url,
                            p.nombre AS plataforma
                          FROM RESENA r
                          INNER JOIN USUARIO u ON u.id = r.id_usuario
                          INNER JOIN VIDEOJUEGO v ON v.id = r.id_videojuego
                          LEFT JOIN USUARIO_JUEGO uj ON uj.id_usuario = r.id_usuario AND uj.id_videojuego = r.id_videojuego
                          LEFT JOIN PLATAFORMA p ON p.id = uj.id_plataforma
                          WHERE r.activa = 1 AND TRIM(COALESCE(r.comentario, "")) <> ""
                          ORDER BY r.fecha_publicacion DESC, r.id DESC
                          LIMIT ' . (int) $limite);
    $stmt->execute();
    $resenas = $stmt->fetchAll();

    foreach ($resenas as &$resena) {
        $resena['puntuacion_estrellas'] = cachePuntuacionUsuarioEstrellas($resena['puntuacion']);
    }

    return $resenas;
}

function cacheJuegosTendenciaInicio(PDO $db, $limite = 10) {
    $limite = max(1, (int) $limite);
    $sqlPuntuaciones = 'SELECT id_videojuego, ROUND(AVG(puntuacion) / 20, 1) AS puntuacion_media
                        FROM RESENA
                        WHERE activa = 1
                        GROUP BY id_videojuego';

    $stmt = $db->prepare('SELECT
                            v.id,
                            v.igdb_id,
                            v.titulo,
                            v.portada_url,
                            COALESCE(r.puntuacion_media, v.puntuacion_igdb) AS puntuacion_visible,
                            COUNT(uj.id) AS altas_recientes,
                            MAX(uj.fecha_registro) AS ultima_alta
                          FROM USUARIO_JUEGO uj
                          INNER JOIN VIDEOJUEGO v ON v.id = uj.id_videojuego
                          LEFT JOIN (' . $sqlPuntuaciones . ') r ON r.id_videojuego = v.id
                          WHERE uj.fecha_registro >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                            AND v.portada_url IS NOT NULL
                            AND TRIM(v.portada_url) <> ""
                            AND COALESCE(r.puntuacion_media, v.puntuacion_igdb) IS NOT NULL
                          GROUP BY v.id, v.igdb_id, v.titulo, v.portada_url, r.puntuacion_media, v.puntuacion_igdb
                          ORDER BY altas_recientes DESC, ultima_alta DESC, puntuacion_visible DESC, v.titulo ASC
                          LIMIT ' . $limite);
    $stmt->execute();
    $juegos = $stmt->fetchAll();

    if (count($juegos) < $limite) {
        $faltan = $limite - count($juegos);
        $idsExcluidos = array_column($juegos, 'id');
        $whereExcluidos = '';
        $params = [];

        if ($idsExcluidos) {
            $whereExcluidos = ' AND v.id NOT IN (' . implode(', ', array_fill(0, count($idsExcluidos), '?')) . ')';
            $params = array_map('intval', $idsExcluidos);
        }

        $stmt = $db->prepare('SELECT
                                v.id,
                                v.igdb_id,
                                v.titulo,
                                v.portada_url,
                                COALESCE(r.puntuacion_media, v.puntuacion_igdb) AS puntuacion_visible
                              FROM VIDEOJUEGO v
                              LEFT JOIN (' . $sqlPuntuaciones . ') r ON r.id_videojuego = v.id
                              WHERE v.portada_url IS NOT NULL
                                AND TRIM(v.portada_url) <> ""
                                AND COALESCE(r.puntuacion_media, v.puntuacion_igdb) IS NOT NULL' . $whereExcluidos . '
                              ORDER BY puntuacion_visible DESC, v.titulo ASC
                              LIMIT ' . $faltan);
        $stmt->execute($params);

        $juegos = array_merge($juegos, $stmt->fetchAll());
    }

    foreach ($juegos as &$juego) {
        $juego['puntuacion_visible'] = isset($juego['puntuacion_visible']) ? (float) $juego['puntuacion_visible'] : null;
    }

    return $juegos;
}

function cacheRecomendacionesInicio(PDO $db, $idUsuario, $limite = 10) {
    $idUsuario = (int) $idUsuario;
    $limite = max(1, (int) $limite);

    if ($idUsuario <= 0) {
        return [];
    }

    $stmt = $db->prepare('SELECT COUNT(*)
                          FROM USUARIO_JUEGO
                          WHERE id_usuario = ?');
    $stmt->execute([$idUsuario]);

    if ((int) $stmt->fetchColumn() < 3) {
        return [];
    }

    $stmt = $db->prepare('SELECT vg.id_genero, COUNT(*) AS total
                          FROM USUARIO_JUEGO uj
                          INNER JOIN VIDEOJUEGO_GENERO vg ON vg.id_videojuego = uj.id_videojuego
                          WHERE uj.id_usuario = ?
                          GROUP BY vg.id_genero
                          ORDER BY total DESC, vg.id_genero ASC
                          LIMIT 3');
    $stmt->execute([$idUsuario]);
    $generos = array_map('intval', array_column($stmt->fetchAll(), 'id_genero'));

    if (!$generos) {
        return [];
    }

    $sqlPuntuaciones = 'SELECT id_videojuego, ROUND(AVG(puntuacion) / 20, 1) AS puntuacion_media
                        FROM RESENA
                        WHERE activa = 1
                        GROUP BY id_videojuego';
    $placeholders = implode(', ', array_fill(0, count($generos), '?'));
    $params = array_merge($generos, [$idUsuario]);

    $stmt = $db->prepare('SELECT
                            v.id,
                            v.igdb_id,
                            v.titulo,
                            v.portada_url,
                            COALESCE(r.puntuacion_media, v.puntuacion_igdb) AS puntuacion_visible,
                            COUNT(DISTINCT vg.id_genero) AS coincidencias_genero
                          FROM VIDEOJUEGO v
                          INNER JOIN VIDEOJUEGO_GENERO vg ON vg.id_videojuego = v.id
                          LEFT JOIN (' . $sqlPuntuaciones . ') r ON r.id_videojuego = v.id
                          WHERE vg.id_genero IN (' . $placeholders . ')
                            AND v.portada_url IS NOT NULL
                            AND TRIM(v.portada_url) <> ""
                            AND COALESCE(r.puntuacion_media, v.puntuacion_igdb) IS NOT NULL
                            AND NOT EXISTS (
                                SELECT 1
                                FROM USUARIO_JUEGO uj
                                WHERE uj.id_usuario = ?
                                  AND uj.id_videojuego = v.id
                            )
                          GROUP BY v.id, v.igdb_id, v.titulo, v.portada_url, r.puntuacion_media, v.puntuacion_igdb
                          ORDER BY coincidencias_genero DESC, puntuacion_visible DESC, v.titulo ASC
                          LIMIT ' . $limite);
    $stmt->execute($params);
    $juegos = $stmt->fetchAll();

    foreach ($juegos as &$juego) {
        $juego['puntuacion_visible'] = isset($juego['puntuacion_visible']) ? (float) $juego['puntuacion_visible'] : null;
    }

    return $juegos;
}

function cacheContarResenasUsuario(PDO $db, $idUsuario) {
    $stmt = $db->prepare('SELECT COUNT(*)
                          FROM RESENA
                          WHERE id_usuario = ? AND activa = 1 AND TRIM(COALESCE(comentario, "")) <> ""');
    $stmt->execute([(int) $idUsuario]);

    return (int) $stmt->fetchColumn();
}

function cacheListarResenasUsuario(PDO $db, $idUsuario, $limite = 12, $offset = 0) {
    $stmt = $db->prepare('SELECT
                            r.comentario,
                            r.puntuacion,
                            r.fecha_publicacion,
                            u.nick,
                            v.igdb_id,
                            v.titulo,
                            v.portada_url,
                            p.nombre AS plataforma
                          FROM RESENA r
                          INNER JOIN USUARIO u ON u.id = r.id_usuario
                          INNER JOIN VIDEOJUEGO v ON v.id = r.id_videojuego
                          LEFT JOIN USUARIO_JUEGO uj ON uj.id_usuario = r.id_usuario AND uj.id_videojuego = r.id_videojuego
                          LEFT JOIN PLATAFORMA p ON p.id = uj.id_plataforma
                          WHERE r.id_usuario = ? AND r.activa = 1 AND TRIM(COALESCE(r.comentario, "")) <> ""
                          ORDER BY r.fecha_publicacion DESC, r.id DESC
                          LIMIT ' . (int) $limite . ' OFFSET ' . (int) $offset);
    $stmt->execute([(int) $idUsuario]);
    $resenas = $stmt->fetchAll();

    foreach ($resenas as &$resena) {
        $resena['puntuacion_estrellas'] = cachePuntuacionUsuarioEstrellas($resena['puntuacion']);
    }

    return $resenas;
}

function cacheFavoritosUsuario(PDO $db, $idUsuario, $limite = 6) {
    $stmt = $db->prepare('SELECT
                            v.igdb_id,
                            v.titulo,
                            v.portada_url
                          FROM USUARIO_JUEGO uj
                          INNER JOIN VIDEOJUEGO v ON v.id = uj.id_videojuego
                          WHERE uj.id_usuario = ? AND uj.favorito = 1
                          ORDER BY
                              CASE
                                  WHEN uj.estado = "jugando" THEN 0
                                  WHEN uj.estado = "completado" THEN 1
                                  WHEN uj.estado = "pendiente" THEN 2
                                  ELSE 3
                              END,
                              v.titulo ASC
                          LIMIT ' . (int) $limite);
    $stmt->execute([(int) $idUsuario]);

    return $stmt->fetchAll();
}

function cacheJuegosUsuarioEsteAno(PDO $db, $idUsuario, $ano = null) {
    $ano = $ano ?: date('Y');
    $stmt = $db->prepare('SELECT COUNT(*)
                          FROM USUARIO_JUEGO
                          WHERE id_usuario = ?
                            AND estado <> "pendiente"
                            AND fecha_inicio IS NOT NULL
                            AND YEAR(fecha_inicio) = ?');
    $stmt->execute([(int) $idUsuario, (int) $ano]);

    return (int) $stmt->fetchColumn();
}

function cacheHistogramaUsuario(PDO $db, $idUsuario) {
    $stmt = $db->prepare('SELECT
                            SUM(CASE WHEN puntuacion <= 20 THEN 1 ELSE 0 END) AS estrella_1,
                            SUM(CASE WHEN puntuacion > 20 AND puntuacion <= 40 THEN 1 ELSE 0 END) AS estrella_2,
                            SUM(CASE WHEN puntuacion > 40 AND puntuacion <= 60 THEN 1 ELSE 0 END) AS estrella_3,
                            SUM(CASE WHEN puntuacion > 60 AND puntuacion <= 80 THEN 1 ELSE 0 END) AS estrella_4,
                            SUM(CASE WHEN puntuacion > 80 THEN 1 ELSE 0 END) AS estrella_5
                          FROM RESENA
                          WHERE id_usuario = ? AND activa = 1');
    $stmt->execute([(int) $idUsuario]);
    $datos = $stmt->fetch() ?: [];

    return [
        1 => (int) ($datos['estrella_1'] ?? 0),
        2 => (int) ($datos['estrella_2'] ?? 0),
        3 => (int) ($datos['estrella_3'] ?? 0),
        4 => (int) ($datos['estrella_4'] ?? 0),
        5 => (int) ($datos['estrella_5'] ?? 0)
    ];
}

function cacheDetalleJuego(PDO $db, $igdbId, $idUsuario = 0, $horas = HORAS_CACHE_JUEGO_IGDB) {
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
    $juego['plataformas_detalle'] = cachePlataformasJuegoDetalle($db, $juego['id']);
    $juego['resumen_resenas'] = cacheResumenResenasJuego($db, $juego['id']);
    $juego['histograma'] = cacheHistogramaJuego($db, $juego['id']);
    $juego['usuario_juego'] = $idUsuario > 0 ? cacheUsuarioJuego($db, $juego['id'], $idUsuario) : null;

    return $juego;
}

function cacheGuardarJuegoBiblioteca(PDO $db, $idUsuario, $idVideojuego, $datos) {
    if (!empty($datos['favorito']) && !cachePuedeMarcarFavorito($db, $idUsuario, $idVideojuego)) {
        return false;
    }

    $idPlataforma = !empty($datos['id_plataforma']) ? (int) $datos['id_plataforma'] : null;

    $stmt = $db->prepare('INSERT INTO USUARIO_JUEGO (
        id_usuario,
        id_videojuego,
        id_plataforma,
        estado,
        horas_jugadas,
        minutos_jugados,
        fecha_inicio,
        fecha_fin,
        favorito
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');

    return $stmt->execute([
        (int) $idUsuario,
        (int) $idVideojuego,
        $idPlataforma,
        $datos['estado'],
        (int) $datos['horas_jugadas'],
        (int) $datos['minutos_jugados'],
        $datos['fecha_inicio'],
        $datos['fecha_fin'],
        !empty($datos['favorito']) ? 1 : 0
    ]);
}

function cacheActualizarJuegoBiblioteca(PDO $db, $idUsuario, $idVideojuego, $datos) {
    if (!empty($datos['favorito']) && !cachePuedeMarcarFavorito($db, $idUsuario, $idVideojuego)) {
        return false;
    }

    $idPlataforma = !empty($datos['id_plataforma']) ? (int) $datos['id_plataforma'] : null;

    $stmt = $db->prepare('UPDATE USUARIO_JUEGO
                          SET id_plataforma = ?,
                              estado = ?,
                              horas_jugadas = ?,
                              minutos_jugados = ?,
                              fecha_inicio = ?,
                              fecha_fin = ?,
                              favorito = ?
                          WHERE id_usuario = ? AND id_videojuego = ?');

    return $stmt->execute([
        $idPlataforma,
        $datos['estado'],
        (int) $datos['horas_jugadas'],
        (int) $datos['minutos_jugados'],
        $datos['fecha_inicio'],
        $datos['fecha_fin'],
        !empty($datos['favorito']) ? 1 : 0,
        (int) $idUsuario,
        (int) $idVideojuego
    ]);
}

function cacheGuardarEstadoRapidoBiblioteca(PDO $db, $idUsuario, $idVideojuego, $estado) {
    $estadosValidos = ['jugando', 'completado', 'pendiente', 'abandonado'];

    if (!in_array($estado, $estadosValidos, true)) {
        return 'error';
    }

    if (cacheUsuarioJuego($db, $idVideojuego, $idUsuario)) {
        return cacheActualizarEstadoJuegoBiblioteca($db, $idUsuario, $idVideojuego, $estado) ? 'actualizado' : 'error';
    }

    $datos = [
        'id_plataforma' => null,
        'estado' => $estado,
        'horas_jugadas' => 0,
        'minutos_jugados' => 0,
        'fecha_inicio' => null,
        'fecha_fin' => null,
        'favorito' => false
    ];

    return cacheGuardarJuegoBiblioteca($db, $idUsuario, $idVideojuego, $datos) ? 'creado' : 'error';
}

function cacheQuitarJuegoBiblioteca(PDO $db, $idUsuario, $idVideojuego) {
    $stmt = $db->prepare('DELETE FROM USUARIO_JUEGO
                          WHERE id_usuario = ? AND id_videojuego = ?');

    return $stmt->execute([(int) $idUsuario, (int) $idVideojuego]);
}

function cacheActualizarEstadoJuegoBiblioteca(PDO $db, $idUsuario, $idVideojuego, $estado) {
    $estadosValidos = ['jugando', 'completado', 'pendiente', 'abandonado'];

    if (!in_array($estado, $estadosValidos, true)) {
        return false;
    }

    $stmt = $db->prepare('UPDATE USUARIO_JUEGO
                          SET estado = ?
                          WHERE id_usuario = ? AND id_videojuego = ?');

    return $stmt->execute([$estado, (int) $idUsuario, (int) $idVideojuego]);
}

function cacheActualizarFavoritoJuegoBiblioteca(PDO $db, $idUsuario, $idVideojuego, $favorito) {
    if ($favorito && !cachePuedeMarcarFavorito($db, $idUsuario, $idVideojuego)) {
        return false;
    }

    $stmt = $db->prepare('UPDATE USUARIO_JUEGO
                          SET favorito = ?
                          WHERE id_usuario = ? AND id_videojuego = ?');

    return $stmt->execute([
        $favorito ? 1 : 0,
        (int) $idUsuario,
        (int) $idVideojuego
    ]);
}

function cacheLimpiarPuntuacionUsuario(PDO $db, $idUsuario, $idVideojuego) {
    $stmt = $db->prepare('SELECT id, comentario
                          FROM RESENA
                          WHERE id_usuario = ? AND id_videojuego = ? AND activa = 1
                          ORDER BY fecha_publicacion DESC, id DESC
                          LIMIT 1');
    $stmt->execute([(int) $idUsuario, (int) $idVideojuego]);
    $resena = $stmt->fetch();

    if (!$resena) {
        return true;
    }

    $comentario = trim((string) ($resena['comentario'] ?? ''));

    if ($comentario === '') {
        $delete = $db->prepare('DELETE FROM RESENA WHERE id = ?');

        return $delete->execute([(int) $resena['id']]);
    }

    $update = $db->prepare('UPDATE RESENA
                            SET puntuacion = NULL
                            WHERE id = ?');

    return $update->execute([(int) $resena['id']]);
}

function cacheGuardarPuntuacionUsuario(PDO $db, $idUsuario, $idVideojuego, $puntuacion) {
    if (!cachePuntuacionResenaValida($puntuacion)) {
        return false;
    }

    $stmt = $db->prepare('SELECT id, activa
                          FROM RESENA
                          WHERE id_usuario = ? AND id_videojuego = ?
                          LIMIT 1');
    $stmt->execute([(int) $idUsuario, (int) $idVideojuego]);
    $resena = $stmt->fetch();

    if ($resena) {
        if (!empty($resena['activa'])) {
            $update = $db->prepare('UPDATE RESENA
                                    SET puntuacion = ?
                                    WHERE id = ?');

            return $update->execute([(int) $puntuacion, (int) $resena['id']]);
        }

        $update = $db->prepare('UPDATE RESENA
                                SET puntuacion = ?, comentario = NULL, fecha_publicacion = NOW(), activa = 1
                                WHERE id = ?');

        return $update->execute([(int) $puntuacion, (int) $resena['id']]);
    }

    $insert = $db->prepare('INSERT INTO RESENA (id_usuario, id_videojuego, puntuacion, comentario)
                            VALUES (?, ?, ?, NULL)');

    return $insert->execute([(int) $idUsuario, (int) $idVideojuego, (int) $puntuacion]);
}

function cacheGuardarResenaUsuario(PDO $db, $idUsuario, $idVideojuego, $puntuacion, $comentario) {
    if (!cachePuntuacionResenaValida($puntuacion) || !cacheComentarioResenaValido($comentario)) {
        return false;
    }

    $resena = cacheResenaUsuario($db, $idUsuario, $idVideojuego);

    if ($resena && !empty($resena['tiene_comentario'])) {
        return false;
    }

    $comentario = trim((string) $comentario);

    if ($resena) {
        $update = $db->prepare('UPDATE RESENA
                                SET puntuacion = ?, comentario = ?, fecha_publicacion = NOW(), activa = 1
                                WHERE id = ?');

        return $update->execute([(int) $puntuacion, $comentario, (int) $resena['id']]);
    }

    $stmt = $db->prepare('SELECT id
                          FROM RESENA
                          WHERE id_usuario = ? AND id_videojuego = ?
                          LIMIT 1');
    $stmt->execute([(int) $idUsuario, (int) $idVideojuego]);
    $idResena = $stmt->fetchColumn();

    if ($idResena) {
        $update = $db->prepare('UPDATE RESENA
                                SET puntuacion = ?, comentario = ?, fecha_publicacion = NOW(), activa = 1
                                WHERE id = ?');

        return $update->execute([(int) $puntuacion, $comentario, (int) $idResena]);
    }

    $insert = $db->prepare('INSERT INTO RESENA (id_usuario, id_videojuego, puntuacion, comentario)
                            VALUES (?, ?, ?, ?)');

    return $insert->execute([(int) $idUsuario, (int) $idVideojuego, (int) $puntuacion, $comentario]);
}

function cacheActualizarResenaUsuario(PDO $db, $idUsuario, $idVideojuego, $puntuacion, $comentario) {
    if (!cachePuntuacionResenaValida($puntuacion) || !cacheComentarioResenaValido($comentario)) {
        return false;
    }

    $resena = cacheResenaUsuario($db, $idUsuario, $idVideojuego);

    if (!$resena || empty($resena['tiene_comentario'])) {
        return false;
    }

    $update = $db->prepare('UPDATE RESENA
                            SET puntuacion = ?, comentario = ?, activa = 1
                            WHERE id = ?');

    return $update->execute([(int) $puntuacion, trim((string) $comentario), (int) $resena['id']]);
}

function cacheEliminarResenaUsuario(PDO $db, $idUsuario, $idVideojuego) {
    $resena = cacheResenaUsuario($db, $idUsuario, $idVideojuego);

    if (!$resena || empty($resena['tiene_comentario'])) {
        return false;
    }

    $transaccionPropia = !$db->inTransaction();

    try {
        if ($transaccionPropia) {
            $db->beginTransaction();
        }

        $reportes = $db->prepare('DELETE FROM REPORTE
                                  WHERE id_resena = ?');
        $reportes->execute([(int) $resena['id']]);

        $delete = $db->prepare('DELETE FROM RESENA
                                WHERE id = ? AND id_usuario = ?');
        $ok = $delete->execute([(int) $resena['id'], (int) $idUsuario]);

        if ($transaccionPropia) {
            $db->commit();
        }

        return $ok;
    } catch (Throwable $e) {
        if ($transaccionPropia && $db->inTransaction()) {
            $db->rollBack();
        }

        return false;
    }
}

function cacheResumenBibliotecaUsuario(PDO $db, $idUsuario) {
    $stmt = $db->prepare("SELECT
                            COUNT(*) AS total,
                            SUM(CASE WHEN favorito = 1 THEN 1 ELSE 0 END) AS favoritos,
                            SUM(CASE WHEN estado IN ('jugando', 'completado', 'abandonado') THEN 1 ELSE 0 END) AS jugados,
                            SUM(CASE WHEN estado = 'jugando' THEN 1 ELSE 0 END) AS jugando,
                            SUM(CASE WHEN estado = 'completado' THEN 1 ELSE 0 END) AS completados,
                            SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) AS pendientes,
                            SUM(CASE WHEN estado = 'abandonado' THEN 1 ELSE 0 END) AS abandonados
                          FROM USUARIO_JUEGO
                          WHERE id_usuario = ?");
    $stmt->execute([(int) $idUsuario]);
    $datos = $stmt->fetch() ?: [];

    return [
        'total' => (int) ($datos['total'] ?? 0),
        'favoritos' => (int) ($datos['favoritos'] ?? 0),
        'jugados' => (int) ($datos['jugados'] ?? 0),
        'jugando' => (int) ($datos['jugando'] ?? 0),
        'completados' => (int) ($datos['completados'] ?? 0),
        'pendientes' => (int) ($datos['pendientes'] ?? 0),
        'abandonados' => (int) ($datos['abandonados'] ?? 0)
    ];
}

function cacheContarBibliotecaUsuario(PDO $db, $idUsuario, $estado = '') {
    $params = [(int) $idUsuario];
    $whereEstado = '';

    if ($estado !== '') {
        $whereEstado = ' AND uj.estado = ?';
        $params[] = $estado;
    }

    $stmt = $db->prepare('SELECT COUNT(*)
                          FROM USUARIO_JUEGO uj
                          WHERE uj.id_usuario = ?' . $whereEstado);
    $stmt->execute($params);

    return (int) $stmt->fetchColumn();
}

function cacheListarBibliotecaUsuario(PDO $db, $idUsuario, $estado = '', $limite = 0, $offset = 0) {
    $params = [(int) $idUsuario];
    $whereEstado = '';

    if ($estado !== '') {
        $whereEstado = ' AND uj.estado = ?';
        $params[] = $estado;
    }

    $stmt = $db->prepare('SELECT
                            uj.estado,
                            uj.horas_jugadas,
                            uj.minutos_jugados,
                            uj.fecha_inicio,
                            uj.fecha_fin,
                            uj.favorito,
                            p.nombre AS plataforma,
                            v.igdb_id,
                            v.titulo,
                            v.portada_url,
                            r.puntuacion,
                            r.comentario
                          FROM USUARIO_JUEGO uj
                          INNER JOIN VIDEOJUEGO v ON v.id = uj.id_videojuego
                          LEFT JOIN PLATAFORMA p ON p.id = uj.id_plataforma
                          LEFT JOIN RESENA r ON r.id = (
                              SELECT r2.id
                              FROM RESENA r2
                              WHERE r2.id_usuario = uj.id_usuario
                                AND r2.id_videojuego = uj.id_videojuego
                                AND r2.activa = 1
                              ORDER BY r2.fecha_publicacion DESC, r2.id DESC
                              LIMIT 1
                          )
                          WHERE uj.id_usuario = ?' . $whereEstado . '
                          ORDER BY
                              CASE
                                  WHEN uj.estado = "jugando" THEN 0
                                  WHEN uj.estado = "pendiente" THEN 1
                                  WHEN uj.estado = "completado" THEN 2
                                  ELSE 3
                              END,
                              uj.favorito DESC,
                              v.titulo ASC'
                              . ($limite > 0 ? ' LIMIT ' . (int) $limite . ' OFFSET ' . (int) $offset : ''));
    $stmt->execute($params);
    $juegos = $stmt->fetchAll();

    foreach ($juegos as &$juego) {
        $juego['puntuacion_usuario'] = cachePuntuacionUsuarioEstrellas($juego['puntuacion']);
        $juego['tiene_resena_texto'] = cacheComentarioResenaPublicado($juego['comentario'] ?? '');
    }

    return $juegos;
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
        'mensaje' => 'Importación completada',
        'importados' => $importados
    ];
}

function cacheContarBusquedaLocal(PDO $db, $busqueda) {
    $busqueda = trim((string) $busqueda);

    if ($busqueda === '') {
        return 0;
    }

    $stmt = $db->prepare('SELECT COUNT(*)
                          FROM VIDEOJUEGO v
                          LEFT JOIN (
                              SELECT id_videojuego, ROUND(AVG(puntuacion) / 20, 1) AS puntuacion_media
                              FROM RESENA
                              WHERE activa = 1
                              GROUP BY id_videojuego
                          ) r ON r.id_videojuego = v.id
                          WHERE v.titulo LIKE ?
                            AND COALESCE(r.puntuacion_media, v.puntuacion_igdb) IS NOT NULL');
    $stmt->execute(['%' . $busqueda . '%']);

    return (int) $stmt->fetchColumn();
}

function cacheBuscarJuegosLocal(PDO $db, $busqueda, $limite = 12, $offset = 0) {
    $busqueda = trim((string) $busqueda);

    if ($busqueda === '') {
        return [];
    }

    $limite = max(1, (int) $limite);
    $offset = max(0, (int) $offset);
    $coincidencia = '%' . $busqueda . '%';
    $inicio = $busqueda . '%';

    $sql = 'SELECT
                v.id,
                v.igdb_id,
                v.titulo,
                v.portada_url,
                v.fecha_lanzamiento,
                v.puntuacion_igdb,
                r.puntuacion_media AS puntuacion_local,
                COALESCE(r.puntuacion_media, v.puntuacion_igdb) AS puntuacion_visible,
                CASE
                    WHEN r.puntuacion_media IS NOT NULL THEN "local"
                    WHEN v.puntuacion_igdb IS NOT NULL THEN "igdb"
                    ELSE "sin_datos"
                END AS origen_puntuacion
            FROM VIDEOJUEGO v
            LEFT JOIN (
                SELECT id_videojuego, ROUND(AVG(puntuacion) / 20, 1) AS puntuacion_media
                FROM RESENA
                WHERE activa = 1
                GROUP BY id_videojuego
            ) r ON r.id_videojuego = v.id
            WHERE v.titulo LIKE ?
              AND COALESCE(r.puntuacion_media, v.puntuacion_igdb) IS NOT NULL
            ORDER BY
                CASE
                    WHEN v.titulo LIKE ? THEN 0
                    WHEN v.titulo LIKE ? THEN 1
                    ELSE 2
                END,
                v.titulo ASC
            LIMIT ' . $limite . ' OFFSET ' . $offset;

    $stmt = $db->prepare($sql);
    $stmt->execute([$coincidencia, $busqueda, $inicio]);

    return $stmt->fetchAll();
}

function cacheImportarBusquedaIgdb(PDO $db, $busqueda, $pagina = 1, $cantidad = 12) {
    $busqueda = trim((string) $busqueda);

    if ($busqueda === '') {
        return [
            'ok' => false,
            'mensaje' => 'No hay término de búsqueda',
            'importados' => 0
        ];
    }

    if (!igdbDisponible()) {
        return [
            'ok' => false,
            'mensaje' => 'No hay credenciales de IGDB configuradas',
            'importados' => 0
        ];
    }

    $respuesta = igdbBuscarJuegos($busqueda, $pagina, $cantidad);

    if ($respuesta === false || $respuesta === null) {
        return [
            'ok' => false,
            'mensaje' => 'No se han podido obtener resultados desde IGDB',
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
        'mensaje' => 'Busqueda completada',
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

function cacheOrdenCatalogo($orden, $direccion = 'normal') {
    $inversa = $direccion === 'inversa';
    $opciones = [
        'puntuacion' => $inversa ? 'COALESCE(r.puntuacion_media, v.puntuacion_igdb, 999) ASC, v.titulo ASC' : 'COALESCE(r.puntuacion_media, v.puntuacion_igdb, -1) DESC, v.titulo ASC',
        'nombre' => $inversa ? 'v.titulo DESC' : 'v.titulo ASC',
        'fecha' => $inversa ? 'v.fecha_lanzamiento ASC, v.titulo ASC' : 'v.fecha_lanzamiento DESC, v.titulo ASC'
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

function cacheListarJuegosCatalogo(PDO $db, $filtros = [], $orden = 'puntuacion', $limite = 12, $offset = 0, $direccion = 'normal') {
    $partes = cacheConstruirFiltrosCatalogo($filtros);
    $sql = 'SELECT DISTINCT
                v.id,
                v.igdb_id,
                v.titulo,
                v.portada_url,
                v.fecha_lanzamiento,
                v.puntuacion_igdb,
                r.puntuacion_media AS puntuacion_local,
                COALESCE(r.puntuacion_media, v.puntuacion_igdb) AS puntuacion_visible,
                CASE
                    WHEN r.puntuacion_media IS NOT NULL THEN "local"
                    WHEN v.puntuacion_igdb IS NOT NULL THEN "igdb"
                    ELSE "sin_datos"
                END AS origen_puntuacion
            FROM VIDEOJUEGO v
            LEFT JOIN (
                SELECT id_videojuego, ROUND(AVG(puntuacion) / 20, 1) AS puntuacion_media
                FROM RESENA
                WHERE activa = 1
                GROUP BY id_videojuego
            ) r ON r.id_videojuego = v.id ' . $partes['joins'];

    if (!empty($partes['where'])) {
        $sql .= ' WHERE ' . implode(' AND ', $partes['where']);
    }

    $sql .= ' ORDER BY ' . cacheOrdenCatalogo($orden, $direccion) . ' LIMIT ' . (int) $limite . ' OFFSET ' . (int) $offset;

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
    $plataformas = $stmt->fetchAll();

    return array_values(array_filter($plataformas, static fn($plataforma) => cachePlataformaPermitida($plataforma['nombre'] ?? '')));
}

function cacheOpcionesAnos(PDO $db) {
    $stmt = $db->query('SELECT DISTINCT YEAR(fecha_lanzamiento) AS anio FROM VIDEOJUEGO WHERE fecha_lanzamiento IS NOT NULL ORDER BY anio DESC');

    return $stmt->fetchAll();
}
