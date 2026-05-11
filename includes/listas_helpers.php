<?php

function listasUsuario(PDO $db, $idUsuario) {
    $stmt = $db->prepare('SELECT
                            l.*,
                            (
                                SELECT COUNT(*)
                                FROM LISTA_VIDEOJUEGO lv
                                WHERE lv.id_lista = l.id
                            ) AS total_juegos
                          FROM LISTA l
                          WHERE l.id_usuario = ?
                          ORDER BY l.fecha_creacion DESC, l.id DESC');
    $stmt->execute([(int) $idUsuario]);

    return $stmt->fetchAll();
}

function listaUsuario(PDO $db, $idUsuario, $idLista) {
    $stmt = $db->prepare('SELECT
                            l.*,
                            (
                                SELECT COUNT(*)
                                FROM LISTA_VIDEOJUEGO lv
                                WHERE lv.id_lista = l.id
                            ) AS total_juegos
                          FROM LISTA l
                          WHERE l.id = ? AND l.id_usuario = ?
                          LIMIT 1');
    $stmt->execute([(int) $idLista, (int) $idUsuario]);

    return $stmt->fetch() ?: null;
}

function listaCrear(PDO $db, $idUsuario, $nombre, $descripcion) {
    $stmt = $db->prepare('INSERT INTO LISTA (id_usuario, nombre, descripcion)
                          VALUES (?, ?, ?)');
    $stmt->execute([(int) $idUsuario, $nombre, $descripcion]);

    return (int) $db->lastInsertId();
}

function listaActualizar(PDO $db, $idUsuario, $idLista, $nombre, $descripcion) {
    $stmt = $db->prepare('UPDATE LISTA
                          SET nombre = ?, descripcion = ?
                          WHERE id = ? AND id_usuario = ?');

    return $stmt->execute([$nombre, $descripcion, (int) $idLista, (int) $idUsuario]);
}

function listaBorrar(PDO $db, $idUsuario, $idLista) {
    $stmt = $db->prepare('DELETE FROM LISTA
                          WHERE id = ? AND id_usuario = ?');

    return $stmt->execute([(int) $idLista, (int) $idUsuario]);
}

function listaTieneJuego(PDO $db, $idUsuario, $idLista, $idVideojuego) {
    $stmt = $db->prepare('SELECT COUNT(*)
                          FROM LISTA l
                          INNER JOIN LISTA_VIDEOJUEGO lv ON lv.id_lista = l.id
                          WHERE l.id = ? AND l.id_usuario = ? AND lv.id_videojuego = ?');
    $stmt->execute([(int) $idLista, (int) $idUsuario, (int) $idVideojuego]);

    return (int) $stmt->fetchColumn() > 0;
}

function listaAnadirJuego(PDO $db, $idUsuario, $idLista, $idVideojuego) {
    if (!listaUsuario($db, $idUsuario, $idLista)) {
        return 'error';
    }

    if (listaTieneJuego($db, $idUsuario, $idLista, $idVideojuego)) {
        return 'existe';
    }

    $stmtOrden = $db->prepare('SELECT COALESCE(MAX(orden), 0) + 1
                               FROM LISTA_VIDEOJUEGO
                               WHERE id_lista = ?');
    $stmtOrden->execute([(int) $idLista]);
    $orden = (int) $stmtOrden->fetchColumn();

    try {
        $stmt = $db->prepare('INSERT INTO LISTA_VIDEOJUEGO (id_lista, id_videojuego, orden)
                              VALUES (?, ?, ?)');
        $stmt->execute([(int) $idLista, (int) $idVideojuego, $orden]);

        return 'ok';
    } catch (PDOException $e) {
        return $e->getCode() === '23000' ? 'existe' : 'error';
    }
}

function listaQuitarJuego(PDO $db, $idUsuario, $idLista, $idVideojuego) {
    $stmt = $db->prepare('DELETE lv
                          FROM LISTA_VIDEOJUEGO lv
                          INNER JOIN LISTA l ON l.id = lv.id_lista
                          WHERE lv.id_lista = ? AND l.id_usuario = ? AND lv.id_videojuego = ?');

    return $stmt->execute([(int) $idLista, (int) $idUsuario, (int) $idVideojuego]);
}

function listaJuegos(PDO $db, $idUsuario, $idLista) {
    $stmt = $db->prepare('SELECT
                            v.id,
                            v.igdb_id,
                            v.titulo,
                            v.portada_url,
                            v.fecha_lanzamiento,
                            lv.orden
                          FROM LISTA l
                          INNER JOIN LISTA_VIDEOJUEGO lv ON lv.id_lista = l.id
                          INNER JOIN VIDEOJUEGO v ON v.id = lv.id_videojuego
                          WHERE l.id = ? AND l.id_usuario = ?
                          ORDER BY lv.orden ASC, v.titulo ASC');
    $stmt->execute([(int) $idLista, (int) $idUsuario]);

    return $stmt->fetchAll();
}

function listaFechaBonita($fecha) {
    if (!$fecha) {
        return 'Sin fecha';
    }

    $marca = strtotime($fecha);

    if ($marca === false) {
        return $fecha;
    }

    $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    return date('j', $marca) . ' ' . $meses[(int) date('n', $marca) - 1] . ' ' . date('Y', $marca);
}

function listaTotalJuegosTexto($total) {
    $total = (int) $total;

    return $total === 1 ? '1 juego' : $total . ' juegos';
}
